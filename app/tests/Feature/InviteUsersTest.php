<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InviteUsersTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_invite_users_with_email()
    {
        $organisation = Organisation::factory()->hasOrganisers(1)->hasFollowers(5)->create();
        $token= $organisation->organisers->first()->createToken('api-token')->plainTextToken;
        $headers = [
            'Authorization' => 'Bearer ' . $token
        ];

        $event = Event::factory()->create([
            'organisation_id' => $organisation->id
        ]);

        $emails = [
            ['email' => 'hallo@telenet.be'],
            ['email' => 'test@telenet.be'],
            ['email' => 'hey@gmail.com']
        ];

        $response = $this->withHeaders($headers)->postJson('/api/events/' . $event->id . '/invite', [
            'emails' => $emails,
            'followers' => true
        ]);

        $response->assertStatus(200);

        Storage::delete(basename($event->image));
        $event->delete();
        $organisation->organisers()->first()->tokens()->delete();
        $organisation->followers()->delete();
        $organisation->delete();
    }
}
