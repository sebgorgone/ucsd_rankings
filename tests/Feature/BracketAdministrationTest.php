<?php

namespace Tests\Feature;

use App\BracketStatus;
use App\MatchupStatus;
use App\Models\Bracket;
use App\Models\Candidate;
use App\Models\Matchup;
use App\Models\User;
use App\Models\Vote;
use App\Services\BracketProgressor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BracketAdministrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_and_admin_can_view_matchup_voters_but_other_users_cannot(): void
    {
        [$bracket, $matchup, $candidate] = $this->bracketWithVote();
        $admin = User::factory()->admin()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($bracket->user)
            ->get(route('brackets.stats', $bracket))
            ->assertOk()
            ->assertSee('Voter Name')
            ->assertSee('voter@example.com')
            ->assertSee($candidate->name);

        $this->actingAs($admin)
            ->get(route('brackets.stats', $bracket))
            ->assertOk()
            ->assertSee('Voter Name');

        $this->actingAs($otherUser)
            ->get(route('brackets.stats', $bracket))
            ->assertForbidden();
    }

    public function test_owner_can_end_candidate_phase_early_when_two_candidates_exist(): void
    {
        $bracket = Bracket::factory()->create([
            'candidate_phase_ends_at' => now()->addDay(),
        ]);
        Candidate::factory()->for($bracket)->count(2)->create();

        $this->actingAs($bracket->user)
            ->put(route('brackets.advance', $bracket))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        $this->assertSame(BracketStatus::Ongoing, $bracket->fresh()->status);
        $this->assertSame(1, $bracket->matchups()->where('status', MatchupStatus::Open)->count());
        $this->assertSame(
            MatchupStatus::Open,
            $bracket->matchups()->where('sequence', 0)->firstOrFail()->status,
        );
    }

    public function test_ending_candidate_phase_early_opens_matchup_one_when_the_bracket_has_byes(): void
    {
        $bracket = Bracket::factory()->create([
            'candidate_phase_ends_at' => now()->addDay(),
        ]);
        Candidate::factory()->for($bracket)->count(5)->create();

        $this->actingAs($bracket->user)
            ->put(route('brackets.advance', $bracket))
            ->assertSessionHasNoErrors();

        $firstMatchup = $bracket->matchups()->where('sequence', 0)->firstOrFail();
        $this->assertSame(MatchupStatus::Open, $firstMatchup->status);
        $this->assertNotNull($firstMatchup->candidate_one_id);
        $this->assertNotNull($firstMatchup->candidate_two_id);
    }

    public function test_candidate_phase_cannot_be_ended_with_fewer_than_two_candidates(): void
    {
        $bracket = Bracket::factory()->create();
        Candidate::factory()->for($bracket)->create();

        $this->actingAs($bracket->user)
            ->put(route('brackets.advance', $bracket))
            ->assertSessionHasErrors('advance');

        $this->assertSame(BracketStatus::Candidate, $bracket->fresh()->status);
    }

    public function test_owner_can_close_an_open_matchup_early_and_advance_the_winner(): void
    {
        [$bracket, $matchup, $candidate] = $this->bracketWithVote();

        $this->actingAs($bracket->user)
            ->put(route('brackets.advance', $bracket))
            ->assertSessionHasNoErrors();

        $this->assertSame(MatchupStatus::Completed, $matchup->fresh()->status);
        $this->assertSame(BracketStatus::Completed, $bracket->fresh()->status);
        $this->assertSame($candidate->id, $bracket->fresh()->winner_candidate_id);
    }

    public function test_non_owner_cannot_advance_a_bracket(): void
    {
        $bracket = Bracket::factory()->create();

        $this->actingAs(User::factory()->create())
            ->put(route('brackets.advance', $bracket))
            ->assertForbidden();
    }

    public function test_admin_can_advance_another_users_bracket(): void
    {
        $bracket = Bracket::factory()->create([
            'candidate_phase_ends_at' => now()->addDay(),
        ]);
        Candidate::factory()->for($bracket)->count(2)->create();

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('brackets.advance', $bracket))
            ->assertSessionHasNoErrors();

        $this->assertSame(BracketStatus::Ongoing, $bracket->fresh()->status);
    }

    public function test_owner_can_pause_and_resume_the_candidate_phase_without_losing_time(): void
    {
        $this->travelTo('2026-01-01 12:00:00');
        $bracket = Bracket::factory()->create([
            'candidate_phase_ends_at' => now()->addMinutes(10),
        ]);

        $this->actingAs($bracket->user)
            ->put(route('brackets.pause', $bracket))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'The phase timer is paused.');

        $this->assertNotNull($bracket->fresh()->paused_at);

        $this->travel(30)->minutes();

        $this->artisan('brackets:progress')
            ->expectsOutput('Processed 0 due bracket(s).')
            ->assertSuccessful();

        $this->actingAs($bracket->user)
            ->put(route('brackets.pause', $bracket))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'The phase timer has resumed.');

        $bracket->refresh();
        $this->assertNull($bracket->paused_at);
        $this->assertSame('2026-01-01 12:40:00', $bracket->candidate_phase_ends_at->format('Y-m-d H:i:s'));
        $this->assertSame(BracketStatus::Candidate, $bracket->status);
    }

    public function test_owner_can_pause_and_resume_an_open_matchup_without_losing_time(): void
    {
        $this->travelTo('2026-01-01 12:00:00');
        [$bracket, $matchup] = $this->bracketWithVote();
        $matchup->update(['closes_at' => now()->addMinutes(10)]);

        $this->actingAs($bracket->user)
            ->put(route('brackets.pause', $bracket))
            ->assertSessionHasNoErrors();

        $this->travel(30)->minutes();
        app(BracketProgressor::class)->progress($bracket);

        $this->assertSame(MatchupStatus::Open, $matchup->fresh()->status);

        $this->actingAs($bracket->user)
            ->put(route('brackets.pause', $bracket))
            ->assertSessionHasNoErrors();

        $this->assertNull($bracket->fresh()->paused_at);
        $this->assertSame('2026-01-01 12:40:00', $matchup->fresh()->closes_at->format('Y-m-d H:i:s'));
    }

    public function test_non_owner_cannot_pause_or_reset_a_bracket(): void
    {
        [$bracket] = $this->bracketWithVote();
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)
            ->put(route('brackets.pause', $bracket))
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->put(route('brackets.reset', $bracket))
            ->assertForbidden();

        $this->assertNull($bracket->fresh()->paused_at);
        $this->assertSame(1, $bracket->matchups()->count());
        $this->assertSame(1, Vote::query()->count());
    }

    public function test_owner_can_reset_a_completed_bracket_to_matchup_one_and_remove_votes(): void
    {
        [$bracket, $matchup, $candidate] = $this->bracketWithVote();
        $originalCandidateIds = collect([
            $matchup->candidate_one_id,
            $matchup->candidate_two_id,
        ])->sort()->values()->all();

        $this->actingAs($bracket->user)
            ->put(route('brackets.advance', $bracket))
            ->assertSessionHasNoErrors();

        $this->assertSame(BracketStatus::Completed, $bracket->fresh()->status);
        $this->assertSame($candidate->id, $bracket->fresh()->winner_candidate_id);

        $this->actingAs($bracket->user)
            ->put(route('brackets.reset', $bracket))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        $resetMatchup = $bracket->matchups()->where('sequence', 0)->firstOrFail();
        $resetCandidateIds = collect([
            $resetMatchup->candidate_one_id,
            $resetMatchup->candidate_two_id,
        ])->sort()->values()->all();

        $this->assertSame(BracketStatus::Ongoing, $bracket->fresh()->status);
        $this->assertNull($bracket->fresh()->winner_candidate_id);
        $this->assertSame(MatchupStatus::Open, $resetMatchup->status);
        $this->assertSame($originalCandidateIds, $resetCandidateIds);
        $this->assertSame(0, Vote::query()->count());
        $this->assertDatabaseMissing('matchups', ['id' => $matchup->id]);
    }

    public function test_admin_can_reset_another_users_bracket(): void
    {
        [$bracket] = $this->bracketWithVote();

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('brackets.reset', $bracket))
            ->assertSessionHasNoErrors();

        $this->assertSame(MatchupStatus::Open, $bracket->matchups()->where('sequence', 0)->firstOrFail()->status);
        $this->assertSame(0, Vote::query()->count());
    }

    /**
     * @return array{Bracket, Matchup, Candidate}
     */
    private function bracketWithVote(): array
    {
        $bracket = Bracket::factory()->create([
            'status' => BracketStatus::Ongoing,
            'candidate_phase_ends_at' => now()->subHour(),
            'bracket_size' => 2,
            'total_rounds' => 1,
            'started_at' => now(),
        ]);
        $candidate = Candidate::factory()->for($bracket)->create();
        $opponent = Candidate::factory()->for($bracket)->create();
        $matchup = Matchup::factory()->for($bracket)->create([
            'candidate_one_id' => $candidate->id,
            'candidate_two_id' => $opponent->id,
            'status' => MatchupStatus::Open,
            'opens_at' => now(),
            'closes_at' => now()->addHour(),
        ]);
        $voter = User::factory()->create([
            'name' => 'Voter Name',
            'email' => 'voter@example.com',
        ]);
        Vote::factory()->for($matchup)->for($candidate)->for($voter)->create();

        return [$bracket, $matchup, $candidate];
    }
}
