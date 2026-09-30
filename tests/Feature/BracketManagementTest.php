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

class BracketManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_create_a_timed_bracket(): void
    {
        $this->freezeTime();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('brackets.store'), [
            'name' => 'Best Library',
            'candidate_phase_days' => '1',
            'candidate_phase_hours' => '2',
            'candidate_phase_minutes' => '3',
            'matchup_days' => '0',
            'matchup_hours' => '1',
            'matchup_minutes' => '30',
            'allow_candidate_submissions' => '1',
        ]);

        $bracket = Bracket::query()->firstOrFail();

        $response->assertRedirect(route('brackets.edit', $bracket));
        $this->assertSame($user->id, $bracket->user_id);
        $this->assertSame(BracketStatus::Candidate, $bracket->status);
        $this->assertTrue($bracket->allow_candidate_submissions);
        $this->assertSame(
            now()->addMinutes(1563)->startOfSecond()->timestamp,
            $bracket->candidate_phase_ends_at->timestamp,
        );
        $this->assertSame(1563, $bracket->candidate_phase_duration_minutes);
        $this->assertSame(90, $bracket->matchup_duration_minutes);
    }

    public function test_owner_and_enabled_community_users_can_add_unique_candidates(): void
    {
        $owner = User::factory()->create();
        $communityUser = User::factory()->create();
        $bracket = Bracket::factory()->for($owner)->create([
            'allow_candidate_submissions' => true,
        ]);

        $this->actingAs($owner)
            ->post(route('brackets.candidates.store', $bracket), ['name' => 'Geisel Library'])
            ->assertSessionHasNoErrors();

        $this->actingAs($communityUser)
            ->post(route('brackets.candidates.store', $bracket), ['name' => 'Price Center'])
            ->assertSessionHasNoErrors();

        $this->actingAs($communityUser)
            ->post(route('brackets.candidates.store', $bracket), ['name' => 'Price Center'])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('candidates', 2);
    }

    public function test_community_users_cannot_add_candidates_when_submissions_are_disabled(): void
    {
        $bracket = Bracket::factory()->create(['allow_candidate_submissions' => false]);

        $this->actingAs(User::factory()->create())
            ->post(route('brackets.candidates.store', $bracket), ['name' => 'Candidate'])
            ->assertForbidden();
    }

    public function test_candidate_phase_can_only_be_extended(): void
    {
        $bracket = Bracket::factory()->create([
            'candidate_phase_duration_minutes' => 120,
            'candidate_phase_ends_at' => now()->addMinutes(120),
        ]);

        $this->actingAs($bracket->user)
            ->patch(route('brackets.update', $bracket), [
                'name' => $bracket->name,
                'candidate_phase_days' => '0',
                'candidate_phase_hours' => '1',
                'candidate_phase_minutes' => '0',
                'matchup_days' => '0',
                'matchup_hours' => '0',
                'matchup_minutes' => '30',
                'allow_candidate_submissions' => '0',
            ])
            ->assertSessionHasErrors('candidate_phase_days');

        $this->actingAs($bracket->user)
            ->patch(route('brackets.update', $bracket), [
                'name' => $bracket->name,
                'candidate_phase_days' => '0',
                'candidate_phase_hours' => '3',
                'candidate_phase_minutes' => '0',
                'matchup_days' => '0',
                'matchup_hours' => '0',
                'matchup_minutes' => '30',
                'allow_candidate_submissions' => '0',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(180, $bracket->fresh()->candidate_phase_duration_minutes);
    }

    public function test_updating_an_ongoing_bracket_changes_future_duration_and_extends_only_the_current_matchup(): void
    {
        $bracket = Bracket::factory()->create([
            'status' => BracketStatus::Ongoing,
            'candidate_phase_ends_at' => now()->subHour(),
            'bracket_size' => 2,
            'total_rounds' => 1,
            'started_at' => now()->subMinute(),
        ]);
        $first = Candidate::factory()->for($bracket)->create();
        $second = Candidate::factory()->for($bracket)->create();
        $matchup = Matchup::factory()->for($bracket)->create([
            'candidate_one_id' => $first->id,
            'candidate_two_id' => $second->id,
            'status' => MatchupStatus::Open,
            'opens_at' => now(),
            'closes_at' => now()->addMinutes(20),
        ]);

        $this->actingAs($bracket->user)
            ->patch(route('brackets.update', $bracket), [
                'name' => 'Updated name',
                'candidate_phase_days' => '1',
                'candidate_phase_hours' => '0',
                'candidate_phase_minutes' => '0',
                'matchup_days' => '0',
                'matchup_hours' => '0',
                'matchup_minutes' => '45',
                'extend_current_matchup_days' => '0',
                'extend_current_matchup_hours' => '1',
                'extend_current_matchup_minutes' => '10',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(45, $bracket->fresh()->matchup_duration_minutes);
        $this->assertSame(
            now()->addMinutes(90)->startOfSecond()->timestamp,
            $matchup->fresh()->closes_at->timestamp,
        );
    }

    public function test_durations_must_total_at_least_one_minute(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('brackets.store'), [
            'name' => 'Invalid Timing',
            'candidate_phase_days' => '0',
            'candidate_phase_hours' => '0',
            'candidate_phase_minutes' => '0',
            'matchup_days' => '0',
            'matchup_hours' => '0',
            'matchup_minutes' => '0',
            'allow_candidate_submissions' => '0',
        ])->assertSessionHasErrors([
            'candidate_phase_days',
            'matchup_days',
        ]);

        $this->assertDatabaseCount('brackets', 0);
    }
}
