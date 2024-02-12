<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class GetUserTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function test_get_user()  {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];

        $response = $this->withHeaders($headers)->get('/api/user');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'first_name',
                'last_name',
                'email',
                'phone_number',
                'street',
                'house_number',
                'city',
                'zip',
                'organisation_id',
                'full_name'
            ]
        ]);

        $user->tokens()->delete();
        $user->delete();
    }
}
