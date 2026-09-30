<?php

namespace Tests\Feature;

use App\Models\Bracket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BracketAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_browse_and_view_brackets(): void
    {
        $bracket = Bracket::factory()->create(['name' => 'Best Campus Food']);

        $this->get(route('brackets.index'))
            ->assertOk()
            ->assertSee('Best Campus Food');

        $this->get(route('brackets.show', $bracket))
            ->assertOk()
            ->assertSee('Best Campus Food');
    }

    public function test_guests_must_sign_in_to_create_or_vote(): void
    {
        $bracket = Bracket::factory()->create();

        $this->get(route('brackets.create'))->assertRedirect(route('login'));
        $this->put('/matchups/1/vote', ['candidate_id' => 1])->assertRedirect(route('login'));
        $this->post(route('brackets.candidates.store', $bracket), ['name' => 'Candidate'])
            ->assertRedirect(route('login'));
    }

    public function test_only_the_owner_or_an_admin_can_edit_a_bracket(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $bracket = Bracket::factory()->for($owner)->create();

        $this->actingAs($owner)->get(route('brackets.edit', $bracket))->assertOk();
        $this->actingAs($admin)->get(route('brackets.edit', $bracket))->assertOk();
        $this->actingAs($otherUser)->get(route('brackets.edit', $bracket))->assertForbidden();
    }

    public function test_brackets_have_no_delete_route(): void
    {
        $bracket = Bracket::factory()->create();

        $this->actingAs($bracket->user)
            ->delete("/brackets/{$bracket->id}")
            ->assertMethodNotAllowed();

        $this->assertDatabaseHas('brackets', ['id' => $bracket->id]);
    }

    public function test_brackets_survive_when_their_creator_deletes_their_account(): void
    {
        $owner = User::factory()->create();
        $bracket = Bracket::factory()->for($owner)->create();

        $owner->delete();

        $this->assertDatabaseHas('brackets', [
            'id' => $bracket->id,
            'user_id' => null,
        ]);
    }
}
