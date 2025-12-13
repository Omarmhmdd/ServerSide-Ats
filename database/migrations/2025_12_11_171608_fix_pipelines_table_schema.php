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
        DB::statement('ALTER TABLE pipelines MODIFY stage_id BIGINT UNSIGNED NULL');
        
        // Drop old global_stages column if it exists (from previous migration)
        if (Schema::hasColumn('pipelines', 'global_stages')) {
            Schema::table('pipelines', function (Blueprint $table) {
                $table->dropColumn('global_stages');
            });
        }
        
        // Add new global_stages enum with correct values
        Schema::table('pipelines', function (Blueprint $table) {
            $table->enum('global_stages', ['applied', 'screen', 'hired', 'rejected'])->nullable()->after('candidate_id');
        });
        
        // Drop old foreign key if exists
        // MySQL doesn't support IF EXISTS for foreign keys, so we use raw SQL
        try {
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'pipelines' 
                AND COLUMN_NAME = 'stage_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            foreach ($foreignKeys as $fk) {
                $constraintName = $fk->CONSTRAINT_NAME;
                DB::statement("ALTER TABLE pipelines DROP FOREIGN KEY `{$constraintName}`");
            }
        } catch (\Exception $e) {
            // No foreign key exists or error occurred, continue
        }
        
        // Add foreign key to custom_stages
        Schema::table('pipelines', function (Blueprint $table) {
            $table->foreign('stage_id')->references('id')->on('custom_stages')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key
        try {
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'pipelines' 
                AND COLUMN_NAME = 'stage_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            foreach ($foreignKeys as $fk) {
                $constraintName = $fk->CONSTRAINT_NAME;
                DB::statement("ALTER TABLE pipelines DROP FOREIGN KEY `{$constraintName}`");
            }
        } catch (\Exception $e) {
            // No foreign key exists or error occurred, continue
        }
        
        // Drop global_stages column
        Schema::table('pipelines', function (Blueprint $table) {
            $table->dropColumn('global_stages');
        });
        
        // Make stage_id NOT NULL again
        DB::statement('ALTER TABLE pipelines MODIFY stage_id BIGINT UNSIGNED NOT NULL');
    }
};
