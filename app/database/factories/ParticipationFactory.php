<?php

namespace Database\Factories;

use App\Models\Badge;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Participation>
 */
class ParticipationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $eventId = Event::all()->random()->id;
        $carpool = fake()->boolean();

        return [
            'paid' => fake()->boolean(),
            'present' => fake()->boolean(),
            'reaction' => fake()->randomElement(['GOING', 'INTERESTED', 'ABSENT']),
            'qr_code' => "This is fake data, so no QR code is generated",
            'checkout_url' => "https://www.mollie.com/payscreen/select-method/7UhSN1zuXS",
            'carpool' => $carpool,
            'carpool_role' => $carpool ? fake()->randomElement(['DRIVER', 'PASSENGER']) : NULL,
            'club_name' => fake()->boolean() ? fake()->company() : NULL,
            'problem' => fake()->boolean() ? fake()->text() : NULL,
            'user_id' => User::all()->whereNull('organisation_id')->random()->id,
            'event_id' => $eventId,
            'badge_id' => fake()->boolean() ? Event::find($eventId)->badge_id : NULL
        ];
    }
}
