<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Badge>
 */
class BadgeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $image = fake()->image();
        Storage::put(basename($image), file_get_contents($image));
        $imagePath = Storage::url(basename($image));

        return [
            'name' => fake()->name(),
            'description' => fake()->text(),
            'image' => $imagePath,
        ];
    }
}
