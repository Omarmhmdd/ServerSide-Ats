<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename is_on_sight to is_on_site in job_roles table
        if (Schema::hasColumn('job_roles', 'is_on_sight') && !Schema::hasColumn('job_roles', 'is_on_site')) {
            DB::statement('ALTER TABLE job_roles CHANGE COLUMN is_on_sight is_on_site TINYINT');
        }

        // Rename role_id to job_role_id in job_skills table
        if (Schema::hasColumn('job_skills', 'role_id') && !Schema::hasColumn('job_skills', 'job_role_id')) {
            DB::statement('ALTER TABLE job_skills CHANGE COLUMN role_id job_role_id BIGINT');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert is_on_site back to is_on_sight
        if (Schema::hasColumn('job_roles', 'is_on_site') && !Schema::hasColumn('job_roles', 'is_on_sight')) {
            DB::statement('ALTER TABLE job_roles CHANGE COLUMN is_on_site is_on_sight TINYINT');
        }

        // Revert job_role_id back to role_id
        if (Schema::hasColumn('job_skills', 'job_role_id') && !Schema::hasColumn('job_skills', 'role_id')) {
            DB::statement('ALTER TABLE job_skills CHANGE COLUMN job_role_id role_id BIGINT');
        }
    }
};
