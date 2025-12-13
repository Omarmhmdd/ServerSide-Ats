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
        // Use raw SQL for MySQL compatibility
        \DB::statement('ALTER TABLE candidates MODIFY portfolio VARCHAR(255) NULL');
        \DB::statement('ALTER TABLE candidates MODIFY linkedin_url VARCHAR(255) NULL');
        \DB::statement('ALTER TABLE candidates MODIFY github_url VARCHAR(255) NULL');
        \DB::statement('ALTER TABLE candidates MODIFY source VARCHAR(255) NULL');
        \DB::statement('ALTER TABLE candidates MODIFY first_name VARCHAR(255) NULL');
        \DB::statement('ALTER TABLE candidates MODIFY last_name VARCHAR(255) NULL');
        \DB::statement('ALTER TABLE candidates MODIFY location VARCHAR(255) NULL');
        \DB::statement('ALTER TABLE candidates MODIFY notes TEXT NULL');
        \DB::statement('ALTER TABLE candidates MODIFY phone VARCHAR(255) NULL');
        \DB::statement('ALTER TABLE candidates MODIFY attachments VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::statement('ALTER TABLE candidates MODIFY portfolio VARCHAR(255) NOT NULL');
        \DB::statement('ALTER TABLE candidates MODIFY linkedin_url VARCHAR(255) NOT NULL');
        \DB::statement('ALTER TABLE candidates MODIFY github_url VARCHAR(255) NOT NULL');
        \DB::statement('ALTER TABLE candidates MODIFY source VARCHAR(255) NOT NULL');
        \DB::statement('ALTER TABLE candidates MODIFY first_name VARCHAR(255) NOT NULL');
        \DB::statement('ALTER TABLE candidates MODIFY last_name VARCHAR(255) NOT NULL');
        \DB::statement('ALTER TABLE candidates MODIFY location VARCHAR(255) NOT NULL');
        \DB::statement('ALTER TABLE candidates MODIFY notes TEXT NOT NULL');
        \DB::statement('ALTER TABLE candidates MODIFY phone VARCHAR(255) NOT NULL');
        \DB::statement('ALTER TABLE candidates MODIFY attachments VARCHAR(255) NOT NULL');
    }
};
