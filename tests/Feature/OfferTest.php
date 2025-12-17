<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\JobRole;
use App\Models\Level;
use App\Models\Offer;
use App\Models\Pipeline;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OfferTest extends TestCase
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
            'name' => 'Recruiter One',
            'email' => 'recruiter1@test.com',
            'password' => Hash::make('password'),
            'phone' => '1112223333',
            'role_id' => $recruiterRole->id
        ]);

        $interviewer = User::create([
            'name' => 'Interviewer One',
            'email' => 'interviewer1@test.com',
            'password' => Hash::make('password'),
            'phone' => '4445556666',
            'role_id' => $interviewerRole->id
        ]);

        $level = Level::create(['name' => 'L5']);

        $this->jobRole = JobRole::create([
            'recruiter_id' => $this->recruiter->id,
            'level_id' => $level->id,
            'interviewer_id' => $interviewer->id,
            'location' => 'New York',
            'title' => 'Product Manager',
            'description' => 'Lead product',
            'is_remote' => false,
            'is_on_site' => true,
        ]);

        $this->candidate = Candidate::create([
            'job_role_id' => $this->jobRole->id,
            'recruiter_id' => $this->recruiter->id,
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice@test.com',
            'processed' => 0
        ]);
    }

    public function test_can_create_offer_successfully()
    {
        $this->actingAs($this->recruiter, 'api');

        $offerData = [
            'candidate_id' => $this->candidate->id,
            'role_id' => $this->jobRole->id,
            'base_salary' => 120000,
            'equity' => 1000,
            'bonus' => 15000,
            'benifits' => 'Full coverage',
            'start_date' => '2024-02-01',
            'contract_type' => 'full_time',
            'status' => 'draft',
            'expiry_date' => '2024-02-15',
        ];

        $response = $this->postJson('/api/v0.1/auth/offer/create', $offerData);

        $response->assertStatus(200)
                 ->assertJson(['data' => 'Offer created']);

        $this->assertDatabaseHas('offers', [
            'candidate_id' => $this->candidate->id,
            'base_salary' => 120000,
        ]);
    }

    public function test_cannot_create_offer_with_missing_fields()
    {
        $this->actingAs($this->recruiter, 'api');

        $invalidData = [
            'candidate_id' => $this->candidate->id,
        ];

        $response = $this->postJson('/api/v0.1/auth/offer/create', $invalidData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['role_id', 'base_salary']);
    }

    public function test_can_get_offer_workflow_data()
    {
        $this->actingAs($this->recruiter, 'api');

        $offer = Offer::create([
            'candidate_id' => $this->candidate->id,
            'role_id' => $this->jobRole->id,
            'created_by' => $this->recruiter->id,
            'base_salary' => 100000,
            'equity' => 500,
            'bonus' => 2000,
            'benifits' => 'Standard',
            'start_date' => '2024-01-01',
            'contract_type' => 'full_time',
            'status' => 'draft',
            'expiry_date' => '2024-02-01',
        ]);

        $response = $this->getJson("/api/v0.1/n8n/offers/{$offer->id}/workflow-data");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'offer_id',
                         'candidate' => ['name', 'email'],
                         'job_role' => ['title'],
                         'offer' => ['base_salary']
                     ]
                 ]);
    }

    public function test_can_get_offer_by_pipeline()
    {
        $this->actingAs($this->recruiter, 'api');

        $pipeline = Pipeline::create([
            'job_role_id' => $this->jobRole->id,
            'candidate_id' => $this->candidate->id,
            'global_stages' => 'offer',
            'interview_id' => null
        ]);

        $offer = Offer::create([
            'candidate_id' => $this->candidate->id,
            'role_id' => $this->jobRole->id,
            'created_by' => $this->recruiter->id,
            'base_salary' => 100000,
            'equity' => 0,
            'bonus' => 0,
            'benifits' => 'None',
            'start_date' => '2024-01-01',
            'contract_type' => 'full_time',
            'status' => 'draft',
            'expiry_date' => '2024-02-01',
        ]);

        $response = $this->getJson("/api/v0.1/n8n/pipelines/{$pipeline->id}/offer");

        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'pipeline_id' => $pipeline->id,
                         'offer_id' => $offer->id
                     ]
                 ]);
    }

    public function test_get_workflow_data_returns_404_for_missing_offer()
    {
        $this->actingAs($this->recruiter, 'api');

        $nonExistentId = 99999;

        $response = $this->getJson("/api/v0.1/n8n/offers/{$nonExistentId}/workflow-data");

        $response->assertStatus(404);
    }
}
