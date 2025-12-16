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
        $table = 'pipelines';

        // 1. Rename stage_id to custom_stage_id if it exists
        if (Schema::hasColumn($table, 'stage_id')) {
            // Drop FK on stage_id first to allow renaming
            $this->dropForeignKey($table, 'stage_id');

            // Rename using raw SQL (MySQL syntax) to avoid doctrine/dbal dependency
            DB::statement("ALTER TABLE {$table} CHANGE stage_id custom_stage_id BIGINT UNSIGNED NULL");
        }
        // If stage_id doesn't exist and custom_stage_id is also missing, create it
        elseif (!Schema::hasColumn($table, 'custom_stage_id')) {
             Schema::table($table, function (Blueprint $table) {
                $table->unsignedBigInteger('custom_stage_id')->nullable()->after('candidate_id');
            });
        }

        // 2. Ensure custom_stage_id is nullable (in case it wasn't handled above)
        DB::statement("ALTER TABLE {$table} MODIFY custom_stage_id BIGINT UNSIGNED NULL");

        // 3. Handle global_stages replacement
        if (Schema::hasColumn($table, 'global_stages')) {
            // We drop the old column and recreate it to ensure the Enum values are fresh
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('global_stages');
            });
        }

        Schema::table($table, function (Blueprint $table) {
            $table->enum('global_stages', ['applied', 'screen', 'offer', 'hired', 'rejected'])
                  ->nullable()
                  ->after('candidate_id');
        });

        // 4. Add the correct Foreign Key for custom_stage_id
        // Drop any existing FK on custom_stage_id just in case
        $this->dropForeignKey($table, 'custom_stage_id');

        Schema::table($table, function (Blueprint $table) {
            $table->foreign('custom_stage_id')
                  ->references('id')
                  ->on('custom_stages')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $table = 'pipelines';

        // Drop FK
        $this->dropForeignKey($table, 'custom_stage_id');

        // Drop global_stages
        if (Schema::hasColumn($table, 'global_stages')) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('global_stages');
            });
        }

        // Rename back to stage_id (optional, implies rolling back the rename)
        if (Schema::hasColumn($table, 'custom_stage_id')) {
            DB::statement("ALTER TABLE {$table} CHANGE custom_stage_id stage_id BIGINT UNSIGNED NOT NULL");
        }
    }

    /**
     * Helper to safely drop foreign keys by column name
     */
    private function dropForeignKey($tableName, $columnName)
    {
        try {
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
                AND COLUMN_NAME = ?
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$tableName, $columnName]);

            foreach ($foreignKeys as $fk) {
                DB::statement("ALTER TABLE {$tableName} DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            }
        } catch (\Exception $e) {
            // Ignore if key doesn't exist
        }
    }
};
