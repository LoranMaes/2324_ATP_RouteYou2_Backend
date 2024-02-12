<?php

namespace Database\Factories;

use App\Models\Checkpoint;
use App\Models\Participation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Achievement>
 */
class AchievementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'completed' => fake()->boolean(),
            'checkpoint_id' => Checkpoint::all()->random()->id,
            'participation_id' => Participation::all()->random()->id
        ];
    }
}
