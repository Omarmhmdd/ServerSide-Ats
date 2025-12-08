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

        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('candidate_id');
            $table->bigInteger('created_by');
            $table->bigInteger('role_id');
            $table->bigInteger('base_salary');
            $table->bigInteger('equity');
            $table->text('bonus');
            $table->text('benifits');
            $table->date('start_date');
            $table->enum('contract_type', ["full_time","part_time","contract"]);
            $table->enum('status', ["accepted","declined","expired","sent","draft"]);
            $table->date('expiry_date');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
