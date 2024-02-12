<?php

namespace Tests\Feature;

use App\Models\Organisation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetOrganisationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_organisation()
    {
        $headers = [
            'Accept' => 'application/json'
        ];

        $organisation = Organisation::factory()->hasOrganisers()->create();

        $response = $this->withHeaders($headers)->get("api/organisations/{$organisation->id}");
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'message',
            'organisation' => [
                'id',
                'name',
                'description',
            ]
        ]);

        $organisation->organisers()->delete();
        $organisation->delete();
    }
}
