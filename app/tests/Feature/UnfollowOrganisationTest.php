<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UnfollowOrganisationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_unfollow_organisation()
    {
        $organisation = Organisation::factory()->create();
        $user = User::factory()->create();
        $token= $user->createToken('api-token')->plainTextToken;
        $headers = [
            'Authorization' => 'Bearer ' . $token
        ];
        $response = $this->withHeaders($headers)->postJson('/api/organisations/' . $organisation->id . '/follow');
        $response->assertStatus(201);
        $response = $this->withHeaders($headers)->deleteJson('/api/organisations/' . $organisation->id . '/follow');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message'
        ]);

        $this->assertDatabaseMissing('followers', [
            'organisation_id' => $organisation->id,
            'user_id' => $user->id
        ]);

        $user->tokens()->delete();
        $user->delete();
        $organisation->delete();
    }
}
