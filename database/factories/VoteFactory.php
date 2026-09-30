<?php

namespace Database\Factories;

use App\Models\Candidate;
use App\Models\Matchup;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vote>
 */
class VoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'matchup_id' => Matchup::factory(),
            'user_id' => User::factory(),
            'candidate_id' => Candidate::factory(),
        ];
    }
}
