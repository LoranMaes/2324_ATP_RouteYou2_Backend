<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostOrganisationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_user_can_create_an_organisation()
    {
        $user = User::factory()->create([
            'organisation_id' => null
        ]);
        $token = $user->createToken('test_token')->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->withHeaders($headers)->postJson('/api/organisations', [
            'name' => 'Test organisation',
            'description' => 'Test organisation description',
        ]);

        $organisationId = $response->json('organisation.id');

        $user->refresh();

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'organisation'
        ]);
        $this->assertEquals($user->organisation_id, $organisationId);
        $this->assertDatabaseHas('organisations', [
            'id' => $organisationId,
            'name' => 'Test organisation',
            'description' => 'Test organisation description',
        ]);

        $user->organisation()->delete();
        $user->tokens()->delete();
        $user->delete();
    }
}
