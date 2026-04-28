<?php

namespace Tests;

use App\Models\User;
use App\Models\Species;
use App\Models\Breed;
use App\Models\Animal;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

abstract class TestCase extends BaseTestCase
{
    use DatabaseMigrations;

    protected string $apiBase = '/api/v1';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesSeeder::class);
    }

    protected function createUser(string $role = 'CITIZEN', array $overrides = []): User
    {
        $user = User::factory()->create(array_merge(['status' => 'ACTIVE'], $overrides));
        $user->assignRole($role);
        return $user;
    }

    protected function actingAsRole(string $role, array $overrides = []): User
    {
        $user = $this->createUser($role, $overrides);
        $this->actingAs($user, 'sanctum');
        return $user;
    }

    protected function tokenFor(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    protected function authHeaders(User $user): array
    {
        return ['Authorization' => 'Bearer ' . $this->tokenFor($user)];
    }

    protected function createAnimalFor(User $owner, array $overrides = []): Animal
    {
        $species = Species::factory()->create();
        $breed   = Breed::factory()->create(['species_id' => $species->id]);

        return Animal::factory()->create(array_merge([
            'user_id'    => $owner->id,
            'species_id' => $species->id,
            'breed_id'   => $breed->id,
            'status'     => 'ACTIVE',
        ], $overrides));
    }

    protected function assertUnauthorized($response): void
    {
        $response->assertStatus(401);
    }

    protected function assertForbidden($response): void
    {
        $response->assertStatus(403);
    }

    protected function assertValidationError($response, string $field): void
    {
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([$field]);
    }
}