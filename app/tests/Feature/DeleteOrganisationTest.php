<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DeleteOrganisationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_delete_organisation()
    {
        $organisation = Organisation::factory()->create([
            'name' => 'Deletable Test Organisation',
        ]);
        $user = User::factory()->create([
            'organisation_id' => $organisation->id,
        ]);

        $token = $user->createToken('api-token')->plainTextToken;
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];

        $response = $this->withHeaders($headers)->delete('/api/organisations/' . $organisation->id);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
        ]);

        $response = $this->withHeaders($headers)->get('/api/organisations/' . $organisation->id);
        $response->assertStatus(404);

        $user->tokens()->delete();
        $user->delete();
    }
}
