<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Participation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateParticipationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_authenticated_user_can_update_participation_in_event()
    {
        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];

        $event = Event::factory()->create();

        $participation = Participation::factory()->create([
            'paid' => false,
            'present' => false,
            'reaction' => 'INTERESTED',
            'carpool' => false,
            'carpool_role' => null,
            'club_name' => 'Test',
            'problem' => null,
            'user_id' => $user->id,
            'event_id' => $event->id,
            'badge_id' => null
        ]);

        $response = $this->withHeaders($headers)->put("/api/events/{$event->id}/participate", [
            'reaction' => 'GOING',
            'carpool' => true,
            'carpool_role' => 'DRIVER',
            'club_name' => 'Test club',
            'problem' => null
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('participations', [
            'id' => $participation->id,
            'present' => false,
            'reaction' => 'GOING',
            'carpool' => true,
            'carpool_role' => 'DRIVER',
            'club_name' => 'Test club',
            'problem' => null,
            'badge_id' => null
        ]);

        Storage::delete(basename($event->image));
        $user->tokens()->delete();
        $user->delete();
        $event->delete();
        $participation->delete();
    }
}
