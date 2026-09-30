<?php

namespace Database\Factories;

use App\Models\Bracket;
use App\Models\Matchup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Matchup>
 */
class MatchupFactory extends Factory
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
            'round' => 1,
            'position' => 0,
            'sequence' => 0,
            'status' => 'pending',
        ];
    }
}
