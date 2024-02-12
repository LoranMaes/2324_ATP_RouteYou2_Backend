<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DeleteUserTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_delete_user() {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);
        $token = $user->createToken('api-token')->plainTextToken;
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->withHeaders($headers)->delete('/api/user');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
        ]);
    }
}
