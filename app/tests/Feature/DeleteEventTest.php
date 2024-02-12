<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DeleteEventTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_delete_event() {
        $user = User::factory()->create([
            'email' => 'test.test@example.com',
            'password' => Hash::make('password'),
            'organisation_id' => 1,
        ]);
        $badge = Badge::factory()->create();
        $event = Event::factory()->create([
            'title' => 'Deletable Test Event',
            'organisation_id' => 1,
            'badge_id' => $badge->id,
        ]);
        $token = $user->createToken('api-token')->plainTextToken;
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];

        $response = $this->withHeaders($headers)->delete('/api/events/' . $event->id);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
        ]);

        $response = $this->withHeaders($headers)->get('/api/events/' . $event->id);
        $response->assertStatus(404);

        $user->tokens()->delete();
        $user->delete();
    }
}
