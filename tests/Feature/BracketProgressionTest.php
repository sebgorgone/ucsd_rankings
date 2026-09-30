<?php

namespace Tests\Feature;

use App\BracketStatus;
use App\MatchupStatus;
use App\Models\Bracket;
use App\Models\Candidate;
use App\Models\User;
use App\Models\Vote;
use App\Services\BracketProgressor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BracketProgressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_candidate_phase_without_two_candidates_completes_without_a_winner(): void
    {
        $bracket = Bracket::factory()->create([
            'candidate_phase_ends_at' => now()->subMinute(),
        ]);
        Candidate::factory()->for($bracket)->create();

        app(BracketProgressor::class)->progress($bracket);

        $bracket->refresh();
        $this->assertSame(BracketStatus::Completed, $bracket->status);
        $this->assertNull($bracket->winner_candidate_id);
        $this->assertNotNull($bracket->completed_at);
    }

    public function test_arbitrary_candidate_count_generates_byes_and_only_one_open_matchup(): void
    {
        $bracket = Bracket::factory()->create([
            'candidate_phase_ends_at' => now()->subMinute(),
        ]);
        Candidate::factory()->for($bracket)->count(5)->create();

        app(BracketProgressor::class)->progress($bracket);

        $bracket->refresh();
        $this->assertSame(8, $bracket->bracket_size);
        $this->assertSame(3, $bracket->total_rounds);
        $this->assertSame(7, $bracket->matchups()->count());
        $this->assertSame(3, $bracket->matchups()->where('status', MatchupStatus::Bye)->count());
        $this->assertSame(1, $bracket->matchups()->where('status', MatchupStatus::Open)->count());
        $this->assertSame(
            MatchupStatus::Open,
            $bracket->matchups()->where('sequence', 0)->firstOrFail()->status,
        );
        $this->get(route('brackets.show', $bracket))
            ->assertOk()
            ->assertSee('Round 1')
            ->assertSee('Final')
            ->assertSee('bracket-connector', false)
            ->assertSee('Current matchup');
    }

    public function test_matchups_advance_sequentially_and_complete_with_a_winner(): void
    {
        $bracket = Bracket::factory()->create([
            'candidate_phase_ends_at' => now()->subMinute(),
            'matchup_duration_minutes' => 10,
        ]);
        Candidate::factory()->for($bracket)->count(2)->create();
        $progressor = app(BracketProgressor::class);
        $progressor->progress($bracket);

        $matchup = $bracket->matchups()->where('status', MatchupStatus::Open)->firstOrFail();
        $winner = $matchup->candidateOne;
        Vote::factory()->for($matchup)->for($winner)->for(User::factory())->create();
        $matchup->update(['closes_at' => now()->subMinute()]);

        $progressor->progress($bracket);

        $bracket->refresh();
        $this->assertSame(BracketStatus::Completed, $bracket->status);
        $this->assertSame($winner->id, $bracket->winner_candidate_id);
    }

    public function test_tied_matchup_pauses_until_owner_resolves_it(): void
    {
        $bracket = Bracket::factory()->create([
            'candidate_phase_ends_at' => now()->subMinute(),
        ]);
        Candidate::factory()->for($bracket)->count(2)->create();
        $progressor = app(BracketProgressor::class);
        $progressor->progress($bracket);

        $matchup = $bracket->matchups()->where('status', MatchupStatus::Open)->firstOrFail();
        $matchup->update(['closes_at' => now()->subMinute()]);
        $progressor->progress($bracket);

        $this->assertSame(BracketStatus::Tied, $bracket->fresh()->status);
        $this->assertSame(MatchupStatus::Tied, $matchup->fresh()->status);

        $this->actingAs($bracket->user)
            ->put(route('matchups.resolve-tie', $matchup), [
                'candidate_id' => $matchup->candidate_one_id,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(BracketStatus::Completed, $bracket->fresh()->status);
        $this->assertSame($matchup->candidate_one_id, $bracket->fresh()->winner_candidate_id);
    }

    public function test_scheduled_command_processes_due_brackets(): void
    {
        Bracket::factory()->create([
            'status' => BracketStatus::Ongoing,
            'candidate_phase_ends_at' => now()->subHour(),
            'bracket_size' => 2,
            'total_rounds' => 1,
            'started_at' => now(),
        ]);

        $bracket = Bracket::factory()->create([
            'candidate_phase_ends_at' => now()->subMinute(),
        ]);
        Candidate::factory()->for($bracket)->count(2)->create();

        $this->artisan('brackets:progress')
            ->expectsOutput('Processed 1 due bracket(s).')
            ->assertSuccessful();

        $this->assertSame(BracketStatus::Ongoing, $bracket->fresh()->status);
        $this->assertSame(1, $bracket->matchups()->where('status', MatchupStatus::Open)->count());
    }
}
