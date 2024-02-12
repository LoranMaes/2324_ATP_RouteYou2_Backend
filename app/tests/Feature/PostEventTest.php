<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostEventTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_post_event()
    {
        Storage::fake('public');
        $badge_image = UploadedFile::fake()->image('badge.jpg');
        $event_image = UploadedFile::fake()->image('event.jpg');
        $eventData = [
            'title' => 'Test event',
            'description' => 'Test description',
            'start' => '2024-05-01 12:00:00',
            'end' => '2024-05-01 18:00:00',
            'price' => 10,
            'max_participant' => 100,
            'city' => 'Brussel',
            'zip' => '1083',
            'street' => 'Basiliekvoorplein',
            'house_number' => '1',
            'visible' => true,
            'event_image' => $event_image,
            'type' => 'GENERAL',
            'badge_name' => 'Test badge',
            'badge_description' => 'Test badge description',
            'badge_image' => $badge_image,
            'routes' => [
                ['id' => 46836981],
                ['id' => 46836982],
                ['id' => 46836983]
            ]
        ];

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'organisation_id' => 1
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];

        $response = $this->withHeaders($headers)->postJson('/api/events', $eventData);

        $response->assertStatus(201);

        $image_name = explode('/', $response['event']['badge']['image'])[2];

        Storage::disk('public')->assertExists($image_name);

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
                'image',
                'type',
                'organisation_id',
                'created_at',
                'updated_at',
                'badge_id',
                'badge' => [
                    'name',
                    'description',
                    'image'
                ],
                'routes'
            ]
        ]);

        $this->assertDatabaseHas('events', [
            'id' => $response['event']['id'],
            'title' => $eventData['title'],
        ]);

        $event = Event::where('id', $response['event']['id'])->first();
        Storage::delete(basename($event->badge->image));
        Storage::delete(basename($event->image));
        $event->badge()->delete();
        $event->delete();
        $user->tokens()->delete();
        $user->delete();
    }
}
