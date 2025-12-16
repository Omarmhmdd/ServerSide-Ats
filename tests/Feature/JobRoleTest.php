<?php

namespace Tests\Feature;

use App\Models\JobRole;
use App\Models\JobSkill;
use App\Models\Level;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JobRoleTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $adminToken;
    protected $level;

    protected function setUp(): void
    {
        parent::setUp();
        $adminRole = UserRole::firstOrCreate(['name' => 'admin']);
        $this->admin = User::forceCreate([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'phone' => '1234567890',
            'role_id' => $adminRole->id,
        ]);
        $this->level = Level::firstOrCreate(['name' => 'Senior']);
        $response = $this->postJson('/api/v0.1/login', ['email' => 'admin@test.com', 'password' => 'password123']);
        $this->adminToken = $response->json('data.token');
    }

    private function authHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . $this->adminToken, 'Accept' => 'application/json'];
    }

    private function createJobRole(array $overrides = []): JobRole
    {
        return JobRole::forceCreate(array_merge([
            'recruiter_id' => $this->admin->id,
            'level_id' => $this->level->id,
            'interviewer_id' => $this->admin->id,
            'location' => 'Remote',
            'title' => 'Test Role',
            'description' => 'Test Description',
            'is_remote' => 1,
            'is_on_site' => 0,
        ], $overrides));
    }

    #[Test]
    public function it_retrieves_all_levels_successfully()
    {
        Level::firstOrCreate(['name' => 'Junior']);
        Level::firstOrCreate(['name' => 'Mid-Level']);
        $response = $this->withHeaders($this->authHeaders())->getJson('/api/v0.1/auth/job_roles/levels');
        $response->assertStatus(200)->assertJsonStructure(['message', 'data' => ['*' => ['id', 'name']]])->assertJsonCount(3, 'data');
    }

    #[Test]
    public function it_lists_all_job_roles_with_correct_structure()
    {
        $this->createJobRole(['location' => 'Toronto', 'title' => 'Frontend Engineer', 'description' => 'React specialist needed']);
        $response = $this->withHeaders($this->authHeaders())->getJson('/api/v0.1/auth/job_roles');
        $response->assertStatus(200)->assertJsonStructure(['message', 'data' => ['*' => ['id', 'title', 'description', 'location', 'is_remote']]])->assertJsonPath('data.0.title', 'Frontend Engineer');
    }

    #[Test]
    public function it_fetches_single_job_role_by_identifier()
    {
        $role = $this->createJobRole(['location' => 'Vancouver', 'title' => 'DevOps Specialist', 'description' => 'Kubernetes expert required', 'is_remote' => 0, 'is_on_site' => 1]);
        $response = $this->withHeaders($this->authHeaders())->getJson("/api/v0.1/auth/job_roles/{$role->id}");
        $response->assertStatus(200)->assertJsonPath('data.0.id', $role->id)->assertJsonPath('data.0.title', 'DevOps Specialist');
    }

    #[Test]
    public function it_creates_new_job_role_with_required_fields()
    {
        $payload = [
            'recruiter_id' => $this->admin->id,
            'level_id' => $this->level->id,
            'interviewer_id' => $this->admin->id,
            'location' => 'Montreal',
            'title' => 'Senior Python Developer',
            'description' => 'Django and FastAPI experience essential',
            'is_remote' => 1,
            'is_on_site' => 0,
        ];
        $response = $this->withHeaders($this->authHeaders())->postJson('/api/v0.1/auth/job_roles/add_update_job_role', $payload);
        $response->assertStatus(200);
        $this->assertDatabaseHas('job_roles', ['title' => 'Senior Python Developer', 'location' => 'Montreal', 'is_remote' => 1, 'is_on_site' => 0]);
    }

    #[Test]
    public function it_associates_skills_with_newly_created_job_role()
    {
        $payload = [
            'recruiter_id' => $this->admin->id,
            'level_id' => $this->level->id,
            'interviewer_id' => $this->admin->id,
            'location' => 'Ottawa',
            'title' => 'Full Stack Developer',
            'description' => 'Node.js and Vue.js proficiency',
            'is_remote' => 1,
            'is_on_site' => 0,
            'skills' => [['name' => 'Node.js', 'nice_to_have' => 0], ['name' => 'Vue.js', 'nice_to_have' => 0], ['name' => 'TypeScript', 'nice_to_have' => 1]],
        ];
        $response = $this->withHeaders($this->authHeaders())->postJson('/api/v0.1/auth/job_roles/add_update_job_role', $payload);
        $response->assertStatus(200);
        $role = JobRole::where('title', 'Full Stack Developer')->first();
        $this->assertDatabaseHas('job_skills', ['job_role_id' => $role->id, 'name' => 'Node.js', 'nice_to_have' => 0]);
        $this->assertDatabaseHas('job_skills', ['job_role_id' => $role->id, 'name' => 'TypeScript', 'nice_to_have' => 1]);
        $this->assertCount(3, $role->skills()->get());
    }

    #[Test]
    public function it_updates_existing_job_role_fields_correctly()
    {
        $role = $this->createJobRole(['location' => 'Calgary', 'title' => 'Junior Developer', 'description' => 'Entry level position', 'is_remote' => 0, 'is_on_site' => 1]);
        $updatePayload = [
            'id' => $role->id,
            'recruiter_id' => $this->admin->id,
            'level_id' => $this->level->id,
            'interviewer_id' => $this->admin->id,
            'location' => 'Edmonton',
            'title' => 'Mid-Level Developer',
            'description' => 'Updated requirements',
            'is_remote' => 1,
            'is_on_site' => 0,
        ];
        $response = $this->withHeaders($this->authHeaders())->postJson('/api/v0.1/auth/job_roles/add_update_job_role', $updatePayload);
        $response->assertStatus(200);
        $this->assertDatabaseHas('job_roles', ['id' => $role->id, 'title' => 'Mid-Level Developer', 'location' => 'Edmonton', 'is_remote' => 1, 'is_on_site' => 0]);
    }

    #[Test]
    public function it_replaces_skills_when_updating_job_role()
    {
        $role = $this->createJobRole(['location' => 'Winnipeg', 'title' => 'Backend Engineer', 'description' => 'API development']);
        JobSkill::create(['job_role_id' => $role->id, 'name' => 'Ruby', 'nice_to_have' => 0]);
        $updatePayload = [
            'id' => $role->id,
            'recruiter_id' => $this->admin->id,
            'level_id' => $this->level->id,
            'interviewer_id' => $this->admin->id,
            'location' => 'Winnipeg',
            'title' => 'Backend Engineer',
            'description' => 'API development',
            'is_remote' => 1,
            'is_on_site' => 0,
            'skills' => [['name' => 'Go', 'nice_to_have' => 0], ['name' => 'PostgreSQL', 'nice_to_have' => 0]],
        ];
        $this->withHeaders($this->authHeaders())->postJson('/api/v0.1/auth/job_roles/add_update_job_role', $updatePayload);
        $this->assertDatabaseMissing('job_skills', ['job_role_id' => $role->id, 'name' => 'Ruby']);
        $this->assertDatabaseHas('job_skills', ['job_role_id' => $role->id, 'name' => 'Go']);
        $this->assertDatabaseHas('job_skills', ['job_role_id' => $role->id, 'name' => 'PostgreSQL']);
    }

    #[Test]
    public function it_removes_job_role_from_database()
    {
        $role = $this->createJobRole(['location' => 'Halifax', 'title' => 'Temporary Role', 'description' => 'Will be deleted']);
        $response = $this->withHeaders($this->authHeaders())->postJson("/api/v0.1/auth/job_roles/delete_role/{$role->id}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('job_roles', ['id' => $role->id]);
    }

    #[Test]
    public function it_returns_empty_array_when_no_job_roles_exist()
    {
        $response = $this->withHeaders($this->authHeaders())->getJson('/api/v0.1/auth/job_roles');
        $response->assertStatus(200)->assertJsonPath('data', []);
    }
}
