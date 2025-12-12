<?php

namespace Database\Seeders;

use App\Models\JobRoles;
use App\Models\Candidate;
use App\Models\Stage;
use App\Models\User;
use App\Models\Level;
use App\Models\UserRole;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, ensure user roles exist
        $roles = ['admin', 'recruiter', 'interviewer'];
        foreach ($roles as $role) {
            UserRole::firstOrCreate(
                ['name' => $role],
                ['name' => $role]
            );
        }

        // Get admin user (or create one)
        $admin = User::where('email', 'admin@test.com')->first();
        if (!$admin) {
            $admin = User::create([
                'name' => 'Admin User',
                'email' => 'admin@test.com',
                'password' => 'password123', // Let Laravel hash it automatically (User model has 'hashed' cast)
                'phone' => '1234567890',
                'role_id' => 1, // admin
            ]);
        } else {
            // Update existing admin password to ensure it's correct
            $admin->password = 'password123';
            $admin->save();
        }

        // Create a test level
        $level = Level::firstOrCreate(
            ['name' => 'Senior'],
            ['name' => 'Senior']
        );

        // Create a test job role
        $jobRole = JobRoles::firstOrCreate(
            ['title' => 'Senior Backend Developer'],
            [
                'recruiter_id' => $admin->id,
                'level_id' => $level->id,
                'hiring_manager_id' => $admin->id,
                'location' => 'Remote',
                'title' => 'Senior Backend Developer',
                'description' => 'Test job role for testing',
                'is_remote' => 1,
                'is_on_sight' => 0,
            ]
        );

        // Create a test candidate
        $candidate = Candidate::firstOrCreate(
            ['email' => 'john.doe@test.com'],
            [
                'recruiter_id' => $admin->id,
                'job_role_id' => $jobRole->id,
                'portfolio' => 'https://portfolio.test.com',
                'linkedin_url' => 'https://linkedin.com/in/test',
                'github_url' => 'https://github.com/test',
                'source' => 'LinkedIn',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@test.com',
                'location' => 'New York',
                'notes' => 'Test candidate',
                'phone' => '9876543210',
                'attachments' => 'cv.pdf',
                'processed' => 0,
            ]
        );

        // Create default stages if they don't exist
        $stages = ['Applied', 'Screen', 'Tech', 'Onsite', 'Offer', 'Hired', 'Rejected'];
        foreach ($stages as $stageName) {
            Stage::firstOrCreate(
                ['name' => $stageName],
                ['name' => $stageName]
            );
        }

        $this->command->info('Test data created successfully!');
        $this->command->info('Job Role ID: ' . $jobRole->id);
        $this->command->info('Candidate ID: ' . $candidate->id);
    }
}
