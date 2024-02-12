<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organisation;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GetEventsTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_events()
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];
        $response = $this->withHeaders($headers)->get('api/events');
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
                                                'participation_id'
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
                ]
            ]
        ]);
    }

    public function test_get_events_with_querystring()
    {
        $organisation = Organisation::factory()->create([
            'name' => 'Fietsersbond',
        ]);

        $event = Event::factory()->create([
            'title' => 'Fietstocht rand rond Brussel',
            'start' => '2023-11-24 10:00:00',
            'end' => '2023-11-24 12:00:00',
            'max_participant' => 500,
            'price' => 5,
            'type' => 'WEBINAR',
            'street' => 'Atomiumplein',
            'house_number' => '1',
            'zip' => 1020,
            'city' => 'Brussel',
            'organisation_id' => $organisation->id,
        ]);

        $response = $this->getJson('api/events?title=fiets&organisation_name=fiets&start=2023-11-24 09:00:00&end=2023-12-24 18:00:00&top_right_lat=50.931467&top_right_long=4.494071&bottom_left_lat=50.777251&bottom_left_long=4.226361&max_participant=600&price=10&type=webinar');

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
                                                'participation_id'
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
                ]
            ]
        ]);

        Storage::delete(basename($event->image));
        $event->delete();
        $organisation->delete();
    }
}
