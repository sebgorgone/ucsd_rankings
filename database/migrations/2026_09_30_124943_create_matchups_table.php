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
        Schema::create('matchups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bracket_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('round');
            $table->unsignedInteger('position');
            $table->unsignedInteger('sequence');
            $table->foreignId('candidate_one_id')->nullable()->constrained('candidates')->nullOnDelete();
            $table->foreignId('candidate_two_id')->nullable()->constrained('candidates')->nullOnDelete();
            $table->foreignId('winner_candidate_id')->nullable()->constrained('candidates')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->timestamps();

            $table->unique(['bracket_id', 'round', 'position']);
            $table->unique(['bracket_id', 'sequence']);
            $table->index(['bracket_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matchups');
    }
};
