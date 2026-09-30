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
        Schema::table('brackets', function (Blueprint $table) {
            $table->boolean('allow_candidate_submissions')->default(false);
            $table->unsignedInteger('candidate_phase_duration_minutes')->default(1440);
            $table->timestamp('candidate_phase_ends_at')->useCurrent();
            $table->unsignedInteger('matchup_duration_minutes')->default(60);
            $table->string('status')->default('candidate');
            $table->unsignedInteger('bracket_size')->nullable();
            $table->unsignedInteger('total_rounds')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('winner_candidate_id')->nullable()->constrained('candidates')->nullOnDelete();

            $table->index(['status', 'candidate_phase_ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brackets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('winner_candidate_id');
            $table->dropIndex(['status', 'candidate_phase_ends_at']);
            $table->dropColumn([
                'allow_candidate_submissions',
                'candidate_phase_duration_minutes',
                'candidate_phase_ends_at',
                'matchup_duration_minutes',
                'status',
                'bracket_size',
                'total_rounds',
                'started_at',
                'completed_at',
            ]);
        });
    }
};
