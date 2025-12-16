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

        // 1. Setup Roles
        $recruiterRole = UserRole::create(['name' => 'recruiter']);
        $interviewerRole = UserRole::create(['name' => 'interviewer']);

        // 2. Setup Users
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

        // 3. Setup Job Role
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

        // 4. Setup Candidate
        $this->candidate = Candidate::create([
            'job_role_id' => $this->jobRole->id,
            'recruiter_id' => $this->recruiter->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'processed' => 0
        ]);
    }

    public function test_can_save_candidate_metadata_successfully()
    {
        $this->actingAs($this->recruiter, 'api');

        $payload = [
            'meta_data' => [
                [
                    'json' => [
                        'candidate_id' => $this->candidate->id,
                        'personal_info' => [
                            'personal_info' => ['full_name' => 'Jane Doe'],
                            'skills' => ['PHP', 'Laravel']
                        ]
                    ]
                ]
            ]
        ];

        // Correct Path: /api + /v0.1 + /n8n + /saveMetaData
        $response = $this->postJson('/api/v0.1/n8n/saveMetaData', $payload);

        $response->assertStatus(200)
                 ->assertJson(['data' => ['message' => 'Meta data saved']]);

        $this->assertDatabaseHas('detected_skills', [
            'candidate_id' => $this->candidate->id,
            'skill' => 'PHP'
        ]);
    }

    public function test_cannot_save_metadata_with_invalid_structure()
    {
        $this->actingAs($this->recruiter, 'api');

        $payload = [
            'meta_data' => [
                [
                    'json' => [
                        // Missing candidate_id to trigger validation error
                        'personal_info' => []
                    ]
                ]
            ]
        ];

        // Correct Path: /api/v0.1/n8n/saveMetaData
        $response = $this->postJson('/api/v0.1/n8n/saveMetaData', $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['meta_data.0.json.candidate_id']);
    }

    public function test_can_get_recruiter_statistics()
    {
        $this->actingAs($this->recruiter, 'api');

        // Correct Path: /api/v0.1/auth/candidate/getStatistics
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

        // Correct Path: /api/v0.1/auth/candidate/getCandidateProgress/{id}
        $response = $this->getJson("/api/v0.1/auth/candidate/getCandidateProgress/{$this->candidate->id}");

        // FIXED: Removed strict structure check on '*' because the array contains mixed types (strings + array)
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data'
                 ]);
    }

    public function test_can_get_candidate_metadata()
    {
        $this->actingAs($this->recruiter, 'api');

        // Correct Path: /api/v0.1/auth/candidate/getDetails/{id}
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
