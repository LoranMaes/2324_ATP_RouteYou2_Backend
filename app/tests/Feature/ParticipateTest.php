<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Participation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ParticipateTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_authenticated_user_can_participate_in_event()
    {
        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;

        $event = Event::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/events/{$event->id}/participate", [
            'reaction' => 'GOING',
            'carpool' => true,
            'carpool_role' => 'DRIVER',
            'club_name' => 'De Tistaertvrienden'
        ]);

        $response->assertStatus(201);

        $qrCodeUrl = $response->json('participation.qr_code');

        $this->assertDatabaseHas('participations', [
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);

        $user->tokens()->delete();
        $user->delete();
        Storage::delete("qr-codes/" . basename($qrCodeUrl));
        Storage::delete(basename($event->image));
        $event->delete();
    }
}
