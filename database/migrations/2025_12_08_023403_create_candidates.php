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

        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('recruiter_id');
            $table->bigInteger('job_role_id');
            $table->bigInteger('meta_data_id');
            $table->string('portfolio');
            $table->string( 'linkedin_url');
            $table->string('github_url');
            $table->string('source');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('location');
            $table->text('notes'); 
            $table->string( 'phone');
            $table->string('attachments');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
