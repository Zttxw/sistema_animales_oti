<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    // REGISTER

    public function test_citizen_can_register_with_valid_data(): void
    {
        $response = $this->postJson("{$this->apiBase}/auth/register", [
            'first_name'            => 'Juan',
            'last_name'             => 'Huanca',
            'identity_document'     => '12345678',
            'email'                 => 'juan@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'user' => ['id', 'first_name', 'last_name', 'email'],
                     'token',
                 ]);

        $this->assertDatabaseHas('users', ['email' => 'juan@example.com']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('Password123!'),
        ]);

        $this->postJson("{$this->apiBase}/auth/login", [
            'email'    => 'test@example.com',
            'password' => 'WrongPassword',
        ])->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_get_profile(): void
    {
        $this->getJson("{$this->apiBase}/auth/me")
             ->assertStatus(401);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user  = $this->createUser('CITIZEN');
        $token = $this->tokenFor($user);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
             ->postJson("{$this->apiBase}/auth/logout")
             ->assertStatus(200);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }
}