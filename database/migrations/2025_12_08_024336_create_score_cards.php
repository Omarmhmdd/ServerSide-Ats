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

        Schema::create('score_cards', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('intreview_id');
            $table->bigInteger('candidate_id');
            $table->string('overall_recommnedation_id');
            $table->text('summary');
            $table->string('written_evidence');
            $table->json('ctieria');// n8n
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('score_cards');
    }
};
