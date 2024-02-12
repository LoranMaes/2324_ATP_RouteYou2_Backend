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

class DeleteCheckpointTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_delete_checkpoint()
    {
        $user = User::factory()->create([
            'organisation_id' => 1,
        ]);
        $event = Event::factory()->create([
            'organisation_id' => $user->organisation_id,
        ]);
        $route = Route::create([
            'routeyou_route_id' => '123456789',
            'event_id' => $event->id,
        ]);
        $checkpoint = Checkpoint::factory()->create([
            'route_id' => $route->id,
        ]);
        $participation = Participation::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);
        $achievement = Achievement::factory()->create([
            'checkpoint_id' => $checkpoint->id,
            'participation_id' => $participation->id,
        ]);
        $response = $this->deleteJson("/api/routes/{$route->id}/checkpoints/{$checkpoint->id}", [], [
            'Authorization' => 'Bearer ' . $user->createToken('api-token')->plainTextToken,
        ]);
        $this->assertDatabaseMissing('checkpoints', [
            'id' => $checkpoint->id,
        ]);
        $this->assertDatabaseMissing('achievements', [
            'id' => $achievement->id,
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Checkpoint deleted successfully',
        ]);

        $user->tokens()->delete();
        $user->delete();
        Storage::delete(basename($event->image));
        $event->delete();
        $route->delete();
    }
}
