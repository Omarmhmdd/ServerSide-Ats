<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up(): void
    {
        Schema::create('job_candidates', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('candidate_id');
            $table->bigInteger('job_role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_candidates');
    }
};
