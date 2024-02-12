<?php

namespace Database\Factories;

use App\Models\Route;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Checkpoint>
 */
class CheckpointFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $latitude = fake()->latitude();
        $longitude = fake()->longitude();

        return [
            'longitude' => $longitude,
            'latitude' => $latitude,
            'qr_code' => "This is fake data, so no QR code is generated",
            'coin' => fake()->numberBetween(0, 100),
            'route_id' => Route::all()->random()->id,
        ];
    }
}
