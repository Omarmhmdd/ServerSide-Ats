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
        // Use raw SQL for MySQL compatibility
        DB::statement('ALTER TABLE pipelines MODIFY intreview_id BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Make it NOT NULL again (but this might fail if there are NULL values)
        DB::statement('ALTER TABLE pipelines MODIFY intreview_id BIGINT UNSIGNED NOT NULL');
    }
};
