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
        // Schema::table('repositories', function (Blueprint $table) {
        //     $table->bigInteger('temp_id');
        // });
        
        Schema::table('repository_technologies', function (Blueprint $table) {
            $table->bigInteger('repo_temp_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repo_tables', function (Blueprint $table) {
            //
        });
    }
};
