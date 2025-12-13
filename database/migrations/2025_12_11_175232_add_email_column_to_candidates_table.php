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
        // Check if email column already exists before adding it
        if (!Schema::hasColumn('candidates', 'email')) {
            Schema::table('candidates', function (Blueprint $table) {
                $table->string('email')->nullable()->after('last_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('candidates', 'email')) {
            Schema::table('candidates', function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }
    }
};