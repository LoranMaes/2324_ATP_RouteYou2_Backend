<?php

namespace Tests\Feature;

use App\Models\Achievement;
use App\Models\Checkpoint;
use App\Models\Event;
use App\Models\Participation;
use App\Models\Route;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostCheckpointTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_post_checkpoint()
    {
        $user = User::factory()->create([
            'organisation_id' => 1,
        ]);
        $token = $user->createToken('api-token')->plainTextToken;
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];

        $event = Event::factory()->create([
            'organisation_id' => $user->organisation_id,
        ]);

        $participation = Participation::factory()->create([
            'paid' => true,
            'present' => true,
            'carpool' => false,
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);

        $route = Route::create([
            'routeyou_route_id' => 1532874,
            'event_id' => $event->id,
        ]);

        $response = $this->postJson("/api/routes/{$route->id}/checkpoints", [
            'longitude' => "-43.470200",
            'latitude' => "-47.672832",
            'coin' => 1,
        ], $headers);

        $checkpointId = $response->json('checkpoint.id');

        $response->assertStatus(201);

        $this->assertDatabaseHas('checkpoints', [
            'longitude' => -43.470200,
            'latitude' => -47.672832,
            'coin' => 1,
            'route_id' => $route->id,
        ]);

        $checkpoint = Checkpoint::find($checkpointId);

        Storage::delete("qr-codes/" . basename($checkpoint->qr_code));
        Storage::delete(basename($event->image));
        $route->delete();
        $participation->delete();
        $event->delete();
        $user->tokens()->delete();
        $user->delete();
    }
}
