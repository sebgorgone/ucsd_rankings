<?php

namespace Tests\Feature;

use App\BracketStatus;
use App\MatchupStatus;
use App\Models\Bracket;
use App\Models\Candidate;
use App\Models\Matchup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VotingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_vote_once_and_change_their_vote_while_matchup_is_open(): void
    {
        [$matchup, $first, $second] = $this->openMatchup();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('matchups.vote', $matchup), ['candidate_id' => $first->id])
            ->assertSessionHasNoErrors();

        $this->actingAs($user)
            ->put(route('matchups.vote', $matchup), ['candidate_id' => $second->id])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('votes', 1);
        $this->assertDatabaseHas('votes', [
            'matchup_id' => $matchup->id,
            'user_id' => $user->id,
            'candidate_id' => $second->id,
        ]);
    }

    public function test_vote_must_target_a_candidate_in_the_open_matchup(): void
    {
        [$matchup] = $this->openMatchup();
        $outsider = Candidate::factory()->create();

        $this->actingAs(User::factory()->create())
            ->put(route('matchups.vote', $matchup), ['candidate_id' => $outsider->id])
            ->assertSessionHasErrors('candidate_id');
    }

    public function test_expired_matchups_reject_votes(): void
    {
        [$matchup, $first] = $this->openMatchup();
        $matchup->update(['closes_at' => now()->subMinute()]);

        $this->actingAs(User::factory()->create())
            ->put(route('matchups.vote', $matchup), ['candidate_id' => $first->id])
            ->assertSessionHasErrors('candidate_id');

        $this->assertDatabaseCount('votes', 0);
    }

    /**
     * @return array{Matchup, Candidate, Candidate}
     */
    private function openMatchup(): array
    {
        $bracket = Bracket::factory()->create([
            'status' => BracketStatus::Ongoing,
            'candidate_phase_ends_at' => now()->subMinute(),
            'bracket_size' => 2,
            'total_rounds' => 1,
            'started_at' => now(),
        ]);
        $first = Candidate::factory()->for($bracket)->create();
        $second = Candidate::factory()->for($bracket)->create();
        $matchup = Matchup::factory()->for($bracket)->create([
            'candidate_one_id' => $first->id,
            'candidate_two_id' => $second->id,
            'status' => MatchupStatus::Open,
            'opens_at' => now()->subMinute(),
            'closes_at' => now()->addHour(),
        ]);

        return [$matchup, $first, $second];
    }
}
