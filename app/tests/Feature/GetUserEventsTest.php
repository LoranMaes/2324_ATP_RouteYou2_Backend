<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GetUserEventsTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_events_from_logged_in_user()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];

        $response = $this->withHeaders($headers)->get('/api/user/events');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'events' => [
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'start',
                        'end',
                        'price',
                        'max_participant',
                        'city',
                        'zip',
                        'street',
                        'house_number',
                        'visible',
                        'image',
                        'type',
                        'payment',
                        'organisation_id',
                        'badge_id',
                        'slug',
                        'status',
                        'going_count'
                    ]
                ]
            ]
        ]);

        $user->tokens()->delete();
        $user->delete();
    }
}
