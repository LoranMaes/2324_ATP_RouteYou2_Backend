<?php

namespace Database\Factories;

use App\Models\Badge;
use App\Models\Event;
use App\Models\Organisation;
use Carbon\Carbon;
use Database\Seeders\EventSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
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

        $minDate = Carbon::now()->subYear();
        $maxDate = Carbon::now()->addYear();

        $start = fake()->dateTimeBetween($minDate, $maxDate);
        $addDays = Carbon::createFromFormat('Y-m-d H:i:s', $start->format('Y-m-d H:i:s'))->addDays(3);

        $end = fake()->dateTimeBetween($start, $addDays);

        return [
            'title' => fake()->company(),
            'description' => fake()->text(),
            'start' => $start,
            'end' => $end,
            'price' => fake()->numberBetween(0.00, 100.00),
            'max_participant' => fake()->numberBetween(0, 10000),
            'city' => fake()->city(),
            'zip' => fake()->numberBetween(1000, 9999),
            'street' => fake()->streetAddress(),
            'house_number' => fake()->numberBetween(1, 400),
            'visible' => fake()->boolean(),
            'image' => $imagePath,
            'type' => $this->faker->randomElement(['GENERAL', 'CLUBEVENT', 'ROUTEBUDDY', 'WEBINAR']),
            'latitude' => fake()->latitude(49.0, 51.0),
            'longitude' => fake()->longitude(2.0, 6.0),
            'organisation_id' => Organisation::all()->random()->id,
            'badge_id' => Badge::all()->random()->id,
         ];
    }
}
