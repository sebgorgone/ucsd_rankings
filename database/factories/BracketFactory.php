<?php

namespace Database\Factories;

use App\Models\Bracket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bracket>
 */
class BracketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->sentence(3),
            'user_id' => User::factory(),
            'allow_candidate_submissions' => false,
            'candidate_phase_duration_minutes' => 1440,
            'candidate_phase_ends_at' => now()->addDay(),
            'matchup_duration_minutes' => 60,
            'status' => 'candidate',
        ];
    }
}
