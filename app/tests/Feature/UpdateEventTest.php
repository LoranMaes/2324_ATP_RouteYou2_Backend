<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Util\Filesystem;
use Tests\TestCase;

class UpdateEventTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_update_event()
    {
        $user = User::factory()->create([
            'email' => 'test@example.be',
            'password' => 'password',
            'organisation_id' => 1
        ]);
        $token = $user->createToken('api-token')->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];

        $badge = Badge::factory()->create();
        $event = Event::factory()->create([
            'organisation_id' => $user->organisation_id,
            'badge_id' => $badge->id,
        ]);

        $response = $this->withHeaders($headers)->putJson("api/events/{$event->id}", [
            'title' => 'update event',
            'description' => 'update description',
            'start' => '2024-05-01 12:00:00',
            'end' => '2024-05-01 18:00:00',
            'price' => 15.99,
            'max_participant' => 75,
            'city' => 'Brussel',
            'zip' => '1083',
            'street' => 'Basiliekvoorplein',
            'house_number' => '1',
            'visible' => true,
            'type' => 'general',
            'badge_name' => 'Updated badge',
            'badge_description' => 'Updated Test description',
            'routes' => [
                ['id' => 987654],
                ['id' => 123456],
            ],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'update event',
            'description' => 'update description',
            'start' => '2024-05-01 12:00:00',
            'end' => '2024-05-01 18:00:00',
            'price' => 15.99,
            'max_participant' => 75,
            'city' => 'Brussel',
            'zip' => '1083',
            'street' => 'Basiliekvoorplein',
            'house_number' => '1',
            'visible' => true,
            'type' => 'GENERAL',
            'badge_id' => $badge->id,
        ]);

        $this->assertDatabaseHas('badges', [
            'id' => $badge->id,
            'name' => 'Updated badge',
            'description' => 'Updated Test description',
        ]);

        foreach ([987654, 123456] as $routeId) {
            $this->assertDatabaseHas('routes', [
                'routeyou_route_id' => $routeId,
                'event_id' => $event->id,
            ]);
        }

        Storage::delete(basename($event->badge->image));
        Storage::delete(basename($event->image));
        $badge->delete();
        $event->delete();
        $user->tokens()->delete();
        $user->delete();
    }
}
