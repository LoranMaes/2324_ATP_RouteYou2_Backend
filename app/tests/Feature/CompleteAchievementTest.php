<?php

namespace Tests\Feature;

use App\Models\Achievement;
use App\Models\Checkpoint;
use App\Models\Event;
use App\Models\Participation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CompleteAchievementTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_complete_achievement()
    {
        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];

        $event = Event::factory()->create();
        $uuid = Str::uuid();
        $checkpoint = Checkpoint::factory()->create([
            "qr_code" => env('APP_URL') . "/storage/qr-codes/" . $uuid . ".svg",
        ]);
        $participation = Participation::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);

        $achievement = Achievement::factory()->create([
            'completed' => false,
            'checkpoint_id' => $checkpoint->id,
            'participation_id' => $participation->id,
        ]);

        $response = $this->withHeaders($headers)
            ->getJson("api/events/{$event->id}/achievements/{$uuid}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('achievements', [
            'completed' => true,
            'checkpoint_id' => $checkpoint->id,
            'participation_id' => $participation->id,
        ]);

        $user->tokens()->delete();
        $user->delete();
        $achievement->delete();
        Storage::delete(basename($event->image));
        $event->delete();
        $checkpoint->delete();
        $participation->delete();
    }
}
