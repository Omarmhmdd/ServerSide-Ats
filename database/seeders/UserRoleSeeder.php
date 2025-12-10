<?php

namespace Database\Seeders;

use App\Models\UserRole;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $roles = ['admin', 'recruiter', 'interviewer'];
        
        foreach ($roles as $role) {
            UserRole::firstOrCreate(
                ['name' => $role],
                ['name' => $role]
            );
        }
    }
    }

