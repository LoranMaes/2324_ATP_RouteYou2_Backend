<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_user_can_register_and_receive_token()
    {
        $userData = [
            'first_name' => 'Jos',
            'last_name' => 'Verstappen',
            'email' => 'test@telenet.be',
            'password' => 'dloAPL!-1368',
            'phone_number' => '0499265798',
            'city' => 'Gent',
            'zip' => 9000,
            'street' => 'Theresianenstraat',
            'house_number' => '50'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201);

        $response->assertJsonStructure([
            'message',
            'token',
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

        $token = $response->json('token');
        $this->assertNotNull($token);

        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
        ]);

        $user = User::where('email', $userData['email'])->first();
        $user->tokens()->delete();
        $user->delete();
    }
}
