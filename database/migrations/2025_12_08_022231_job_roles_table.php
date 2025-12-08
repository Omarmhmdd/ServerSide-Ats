<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('job_roles', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('recruiter_id');
            $table->bigInteger('level_id');
            $table->bigInteger('hiring_manager_id');
            $table->string('location');
            $table->string('title');
            $table->text('description');
            $table->tinyInteger('is_remote');
            $table->tinyInteger('is_on_sight');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_roles');
    }
};
