<?php

namespace Database\Factories;

use App\Models\Bracket;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Candidate>
 */
class CandidateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bracket_id' => Bracket::factory(),
            'submitted_by' => User::factory(),
            'name' => fake()->unique()->words(2, true),
            'seed' => null,
        ];
    }
}
