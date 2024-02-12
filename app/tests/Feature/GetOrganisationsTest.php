<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetOrganisationsTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_organisations()
    {
        $headers = [
            'Accept' => 'application/json'
        ];

        $response = $this->withHeaders($headers)->get("api/organisations");
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'message',
            'organisations' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                ]
            ]
        ]);
    }
}
