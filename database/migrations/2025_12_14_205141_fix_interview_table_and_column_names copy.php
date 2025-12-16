<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Rename intreviews to interviews only if intreviews exists and interviews doesn't
        if (Schema::hasTable('intreviews') && !Schema::hasTable('interviews')) {
            Schema::rename('intreviews', 'interviews');
        }
        
        // Rename interviewer column only if it exists with old name
        if (Schema::hasTable('interviews') && Schema::hasColumn('interviews', 'intreveiwer_id') && !Schema::hasColumn('interviews', 'interviewer_id')) {
            DB::statement('ALTER TABLE interviews CHANGE COLUMN intreveiwer_id interviewer_id BIGINT');
        }
        
        // Rename pipeline interview column only if it exists with old name
        if (Schema::hasTable('pipelines') && Schema::hasColumn('pipelines', 'intreview_id') && !Schema::hasColumn('pipelines', 'interview_id')) {
            DB::statement('ALTER TABLE pipelines CHANGE COLUMN intreview_id interview_id BIGINT UNSIGNED NULL');
        }
        
        // Rename score_cards interview column only if it exists with old name
        if (Schema::hasTable('score_cards') && Schema::hasColumn('score_cards', 'intreview_id') && !Schema::hasColumn('score_cards', 'interview_id')) {
            DB::statement('ALTER TABLE score_cards CHANGE COLUMN intreview_id interview_id BIGINT');
        }
    }

    public function down(): void
    {
        // Revert score_cards column
        if (Schema::hasTable('score_cards') && Schema::hasColumn('score_cards', 'interview_id') && !Schema::hasColumn('score_cards', 'intreview_id')) {
            DB::statement('ALTER TABLE score_cards CHANGE COLUMN interview_id intreview_id BIGINT');
        }
        
        // Revert pipelines column
        if (Schema::hasTable('pipelines') && Schema::hasColumn('pipelines', 'interview_id') && !Schema::hasColumn('pipelines', 'intreview_id')) {
            DB::statement('ALTER TABLE pipelines CHANGE COLUMN interview_id intreview_id BIGINT UNSIGNED NULL');
        }
        
        // Revert interviews column
        if (Schema::hasTable('interviews') && Schema::hasColumn('interviews', 'interviewer_id') && !Schema::hasColumn('interviews', 'intreveiwer_id')) {
            DB::statement('ALTER TABLE interviews CHANGE COLUMN interviewer_id intreveiwer_id BIGINT');
        }
        
        // Revert table name
        if (Schema::hasTable('interviews') && !Schema::hasTable('intreviews')) {
            Schema::rename('interviews', 'intreviews');
        }
    }
};
