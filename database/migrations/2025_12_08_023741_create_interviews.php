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

        Schema::create('intreviews', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('intreveiwer_id');
            $table->bigInteger('job_role_id');
            $table->bigInteger('candidate_id');
            $table->string('type');
            $table->timestamp('schedule');
            $table->bigInteger('duration');// in min
            $table->string('meeting_link');
            $table->string('rubric');
            $table->text('notes');
            $table->enum('status', ["no show","completed","canceled","posptponed","pending"]);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intreviews');
    }
};
