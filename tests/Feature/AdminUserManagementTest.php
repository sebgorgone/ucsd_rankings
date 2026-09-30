<?php

namespace Tests\Feature;

use App\BracketStatus;
use App\MatchupStatus;
use App\Models\Bracket;
use App\Models\Candidate;
use App\Models\Matchup;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admins_can_view_user_management_pages(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee($user->email);

        $this->actingAs($admin)
            ->get(route('admin.users.show', $user))
            ->assertOk()
            ->assertSee($user->name);

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.users.show', $admin))
            ->assertForbidden();
    }

    public function test_admin_can_see_brackets_a_user_owns_and_has_voted_on(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $ownedBracket = Bracket::factory()->for($user)->create(['name' => 'Owned Bracket']);
        $votedBracket = Bracket::factory()->create([
            'name' => 'Voted Bracket',
            'status' => BracketStatus::Ongoing,
            'candidate_phase_ends_at' => now()->subHour(),
            'bracket_size' => 2,
            'total_rounds' => 1,
            'started_at' => now(),
        ]);
        $candidate = Candidate::factory()->for($votedBracket)->create();
        $opponent = Candidate::factory()->for($votedBracket)->create();
        $matchup = Matchup::factory()->for($votedBracket)->create([
            'candidate_one_id' => $candidate->id,
            'candidate_two_id' => $opponent->id,
            'status' => MatchupStatus::Open,
            'opens_at' => now(),
            'closes_at' => now()->addHour(),
        ]);
        Vote::factory()->for($matchup)->for($candidate)->for($user)->create();

        $this->actingAs($admin)
            ->get(route('admin.users.show', $user))
            ->assertOk()
            ->assertSee($ownedBracket->name)
            ->assertSee($votedBracket->name)
            ->assertSee('1 total votes')
            ->assertSee('Voted in 1 matchup(s)');
    }
}
