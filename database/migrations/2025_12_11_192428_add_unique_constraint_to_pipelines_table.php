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
        // First, clean up duplicate pipelines (keep the most recent one)
        $duplicates = DB::select("
            SELECT candidate_id, job_role_id, COUNT(*) as count
            FROM pipelines
            GROUP BY candidate_id, job_role_id
            HAVING count > 1
        ");
        
        foreach ($duplicates as $duplicate) {
            // Get all pipelines for this candidate + job_role combination
            $pipelines = DB::table('pipelines')
                ->where('candidate_id', $duplicate->candidate_id)
                ->where('job_role_id', $duplicate->job_role_id)
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Keep the first (most recent) one, delete the rest
            $keepId = $pipelines->first()->id;
            $deleteIds = $pipelines->skip(1)->pluck('id')->toArray();
            
            if (!empty($deleteIds)) {
                DB::table('pipelines')->whereIn('id', $deleteIds)->delete();
            }
        }
        
        // Now add the unique constraint
        Schema::table('pipelines', function (Blueprint $table) {
            $table->unique(['candidate_id', 'job_role_id'], 'unique_candidate_job_role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pipelines', function (Blueprint $table) {
            $table->dropUnique('unique_candidate_job_role');
        });
    }
};
