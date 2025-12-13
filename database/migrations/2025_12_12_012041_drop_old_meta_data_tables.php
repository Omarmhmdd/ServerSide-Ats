<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('candidate_achievements');
        Schema::dropIfExists('candidate_certifications');
        Schema::dropIfExists('candidate_education');
        Schema::dropIfExists('candidate_languages');
        Schema::dropIfExists('candidate_projects');
        Schema::dropIfExists('candidate_repositories');
        Schema::dropIfExists('candidate_skills');
        Schema::dropIfExists('candidate_work_experience');
        Schema::dropIfExists('candidate_volunteering');
        Schema::dropIfExists('stages');
        Schema::dropIfExists('job');
        Schema::dropIfExists('job_batches');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
