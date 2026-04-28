<?php

namespace Tests\Feature\Users;

use Tests\TestCase;
use App\Models\User;


/**
 * @property string $baseUrl
 */

class UserTest extends TestCase
{
    // ════════════════════════════════════════════════════════════════════
    // INDEX
    // ════════════════════════════════════════════════════════════════════

    /** @test */
    public function admin_can_list_users(): void
    {
        $this->actingAsRole('ADMIN');
        User::factory()->count(5)->create();

        $this->getJson("{$this->baseUrl}/users")
             ->assertStatus(200)
             ->assertJsonStructure(['data']);
    }

    /** @test */
    public function citizen_cannot_list_users(): void
    {
        $this->actingAsRole('CITIZEN');

        $this->getJson("{$this->baseUrl}/users")
             ->assertStatus(403);
    }

    // ════════════════════════════════════════════════════════════════════
    // SHOW
    // ════════════════════════════════════════════════════════════════════

    /** @test */
    public function user_can_view_own_profile(): void
    {
        $user = $this->actingAsRole('CITIZEN');

        $this->getJson("{$this->baseUrl}/users/{$user->id}")
             ->assertStatus(200)
             ->assertJsonPath('id', $user->id);
    }

    /** @test */
    public function citizen_cannot_view_another_users_profile(): void
    {
        $other = User::factory()->create();
        $this->actingAsRole('CITIZEN');

        $this->getJson("{$this->baseUrl}/users/{$other->id}")
             ->assertStatus(403);
    }

    // ════════════════════════════════════════════════════════════════════
    // UPDATE STATUS
    // ════════════════════════════════════════════════════════════════════

    /** @test */
    public function admin_can_suspend_user(): void
    {
        $this->actingAsRole('ADMIN');
        $target = $this->createUser('CITIZEN');

        $this->patchJson("{$this->baseUrl}/users/{$target->id}/status", [
            'status' => 'SUSPENDED',
        ])->assertStatus(200)
          ->assertJsonPath('status', 'SUSPENDED');
    }

    /** @test */
    public function user_cannot_suspend_themselves(): void
    {
        $user = $this->actingAsRole('COORDINATOR');

        $this->patchJson("{$this->baseUrl}/users/{$user->id}/status", [
            'status' => 'SUSPENDED',
        ])->assertStatus(403);
    }

    // ════════════════════════════════════════════════════════════════════
    // UPDATE ROLE
    // ════════════════════════════════════════════════════════════════════


    /** @test */
    public function admin_can_change_user_role(): void
    {
        $this->actingAsRole('ADMIN');
        $target = $this->createUser('CITIZEN');

        $this->patchJson("{$this->baseUrl}/users/{$target->id}/role", [
            'role' => 'VETERINARIAN',
        ])->assertStatus(200);

        $this->assertTrue($target->fresh()->hasRole('VETERINARIAN'));
    }

    /** @test */
    public function coordinator_cannot_change_user_role(): void
    {
        $this->actingAsRole('COORDINATOR');
        $target = $this->createUser('CITIZEN');

        $this->patchJson("{$this->baseUrl}/users/{$target->id}/role", [
            'role' => 'ADMIN',
        ])->assertStatus(403);
    }
}