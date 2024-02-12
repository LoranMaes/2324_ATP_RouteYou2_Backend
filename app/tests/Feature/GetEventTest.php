<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GetEventTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function test_get_event()
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];
        $event = Event::factory()->create();

        $response = $this->withHeaders($headers)->get("api/events/{$event->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'event' => [
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
                'type',
                'organisation_id',
                'badge_id',
                'created_at',
                'updated_at',
                'routes' => [
                    '*' => [
                        'id',
                        'event_id',
                        'checkpoints' => [
                            '*' => [
                                'id',
                                'longitude',
                                'latitude',
                                'coin',
                                'route_id',
                                'achievements' => [
                                    '*' => [
                                        'completed',
                                        'checkpoint_id',
                                        'user_id',
                                        'event_id'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'badge' => [
                    'id',
                    'name',
                    'description',
                    'image',
                ],
                'participations' => [
                    '*' => [
                        'paid',
                        'present',
                        'reaction',
                        'club_name',
                        'carpool',
                        'carpool_role',
                        'problem',
                        'user_id',
                        'event_id',
                        'badge_id',
                    ]
                ]

            ]
        ]);

        Storage::delete(basename($event->image));
        $event->delete();
    }
}
