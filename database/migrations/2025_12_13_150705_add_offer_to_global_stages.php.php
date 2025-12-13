<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
        
        DB::statement("ALTER TABLE pipelines MODIFY COLUMN global_stages ENUM('applied', 'screen', 'offer', 'hired', 'rejected') NULL");
    }

    
    public function down(): void
    {
        // Revert back to original enum values (remove 'offer')
        DB::statement("ALTER TABLE pipelines MODIFY COLUMN global_stages ENUM('applied', 'screen', 'hired', 'rejected') NULL");
    }
};