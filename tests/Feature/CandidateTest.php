<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\JobRole;
use App\Models\Level;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CandidateTest extends TestCase
{
    use RefreshDatabase;

    protected $recruiter;
    protected $jobRole;
    protected $candidate;

    protected function setUp(): void
    {
        parent::setUp();

        $recruiterRole = UserRole::create(['name' => 'recruiter']);
        $interviewerRole = UserRole::create(['name' => 'interviewer']);

        $this->recruiter = User::create([
            'name' => 'Test Recruiter',
            'email' => 'recruiter@test.com',
            'password' => Hash::make('password'),
            'phone' => '1234567890',
            'role_id' => $recruiterRole->id
        ]);

        $interviewer = User::create([
            'name' => 'Test Interviewer',
            'email' => 'interviewer@test.com',
            'password' => Hash::make('password'),
            'phone' => '0987654321',
            'role_id' => $interviewerRole->id
        ]);

        $level = Level::create(['name' => 'L4']);

        $this->jobRole = JobRole::create([
            'recruiter_id' => $this->recruiter->id,
            'level_id' => $level->id,
            'interviewer_id' => $interviewer->id,
            'location' => 'Remote',
            'title' => 'Backend Engineer',
            'description' => 'Great job',
            'is_remote' => true,
            'is_on_site' => false,
        ]);

        $this->candidate = Candidate::create([
            'job_role_id' => $this->jobRole->id,
            'recruiter_id' => $this->recruiter->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'processed' => 0
        ]);
    }

    public function test_can_create_interview_for_candidate()
    {
        $this->actingAs($this->recruiter, 'api');

        $interviewer = User::where('role_id', 4)->first() ?? User::factory()->create(['role_id' => 4]);

        $payload = [
            'candidate_id' => $this->candidate->id,
            'job_role_id' => $this->jobRole->id,
            'interviewer_id' => $interviewer->id,
            'type' => 'Screening',
            'schedule' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'duration' => 30,
            'rubric' => 'a',
            'meeting_link' => 'https://meet.google.com/abc-defg-hij',
            'notes' => 'Initial screening interview',
            'status' => 'pending'
        ];

        $response = $this->postJson('/api/v0.1/interviews', $payload);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'data' => [
                         'interview' => [
                             'id',
                             'candidate_id',
                             'status',
                             'schedule'
                         ]
                     ]
                 ]);

        $this->assertDatabaseHas('interviews', [
            'candidate_id' => $this->candidate->id,
            'job_role_id' => $this->jobRole->id,
            'type' => 'Screening'
        ]);

        $this->assertDatabaseHas('pipelines', [
            'candidate_id' => $this->candidate->id,
            'job_role_id' => $this->jobRole->id,
            'global_stages' => 'screen'
        ]);
    }

    public function test_cannot_save_metadata_with_invalid_structure()
    {
        $this->actingAs($this->recruiter, 'api');

        $payload = [
            'meta_data' => [
                [
                    'json' => [
                        'personal_info' => []
                    ]
                ]
            ]
        ];

        $response = $this->postJson('/api/v0.1/n8n/saveMetaData', $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['meta_data.0.json.candidate_id']);
    }

    public function test_can_get_recruiter_statistics()
    {
        $this->actingAs($this->recruiter, 'api');

        $response = $this->getJson('/api/v0.1/auth/candidate/getStatistics');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'applications',
                         'offers',
                         'hired',
                         'rejected'
                     ]
                 ]);
    }

    public function test_can_get_candidate_progress()
    {
        $this->actingAs($this->recruiter, 'api');

        $response = $this->getJson("/api/v0.1/auth/candidate/getCandidateProgress/{$this->candidate->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data'
                 ]);
    }

    public function test_can_get_candidate_metadata()
    {
        $this->actingAs($this->recruiter, 'api');

        $response = $this->getJson("/api/v0.1/auth/candidate/getDetails/{$this->candidate->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'candidate',
                         'repositories',
                         'education',
                         'detected_skills'
                     ]
                 ]);
    }
}
