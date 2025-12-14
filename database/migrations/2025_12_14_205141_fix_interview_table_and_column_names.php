<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        
        Schema::rename('intreviews', 'interviews');
        
        
        Schema::table('interviews', function (Blueprint $table) {
            $table->renameColumn('intreveiwer_id', 'interviewer_id');
        });
        
        
        Schema::table('pipelines', function (Blueprint $table) {
            $table->renameColumn('intreview_id', 'interview_id');
        });
        
        
        Schema::table('score_cards', function (Blueprint $table) {
            $table->renameColumn('intreview_id', 'interview_id');
        });
    }

    public function down(): void
    {
        
        Schema::table('score_cards', function (Blueprint $table) {
            $table->renameColumn('interview_id', 'intreview_id');
        });
        
        Schema::table('pipelines', function (Blueprint $table) {
            $table->renameColumn('interview_id', 'intreview_id');
        });
        
        Schema::table('interviews', function (Blueprint $table) {
            $table->renameColumn('interviewer_id', 'intreveiwer_id');
        });
        
        Schema::rename('interviews', 'intreviews');
    }
};