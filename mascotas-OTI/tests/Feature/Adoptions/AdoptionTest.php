<?php

namespace Tests\Feature\Adoptions;

use Tests\TestCase;
use App\Models\Adoption;

class AdoptionTest extends TestCase
{

    // ════════════════════════════════════════════════════════════════════
    // STORE — publicar animal en adopción
    // ════════════════════════════════════════════════════════════════════

    public function test_coordinator_can_publish_animal_for_adoption(): void
    {
        $this->actingAsRole('COORDINATOR');
        $owner  = $this->createUser('CITIZEN');
        $animal = $this->createAnimalFor($owner);

        $response = $this->postJson("{$this->apiBase}/adoptions", [
            'animal_id'   => $animal->id,
            'description' => 'Buen perro, busca hogar',
            'contact'     => '984000000',
        ]);

        $response->assertStatus(201);

        // El animal debe cambiar a FOR_ADOPTION
        $this->assertDatabaseHas('animals', [
            'id'     => $animal->id,
            'status' => 'FOR_ADOPTION',
        ]);

        // Se registra en adoptions
        $this->assertDatabaseHas('adoptions', [
            'animal_id' => $animal->id,
            'status'    => 'AVAILABLE',
        ]);
    }

    public function test_citizen_cannot_publish_animal_for_adoption(): void
    {
        $owner  = $this->actingAsRole('CITIZEN');
        $animal = $this->createAnimalFor($owner);

        $this->postJson("{$this->apiBase}/adoptions", ['animal_id' => $animal->id])
             ->assertStatus(403);
    }

    public function test_same_animal_cannot_be_published_twice(): void
    {
        $this->actingAsRole('COORDINATOR');
        $owner  = $this->createUser('CITIZEN');
        $animal = $this->createAnimalFor($owner);

        Adoption::factory()->create(['animal_id' => $animal->id]);

        $this->postJson("{$this->apiBase}/adoptions", ['animal_id' => $animal->id])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['animal_id']);
    }

    // ════════════════════════════════════════════════════════════════════
    // UPDATE STATUS — flujo de negocio
    // ════════════════════════════════════════════════════════════════════

    public function test_coordinator_can_complete_adoption(): void
    {
        $coordinator = $this->actingAsRole('COORDINATOR');
        $owner       = $this->createUser('CITIZEN');
        $animal      = $this->createAnimalFor($owner);
        $adopter     = $this->createUser('CITIZEN');
        $adoption    = Adoption::factory()->create([
            'animal_id' => $animal->id,
            'status'    => 'IN_PROCESS',
        ]);

        $this->patchJson("{$this->apiBase}/adoptions/{$adoption->id}/status", [
            'status'     => 'ADOPTED',
            'adopted_by' => $adopter->id,
            'adopted_at' => now()->toDateString(),
        ])->assertStatus(200)
          ->assertJsonPath('status', 'ADOPTED');

        // El animal cambia de propietario
        $this->assertDatabaseHas('animals', [
            'id'      => $animal->id,
            'user_id' => $adopter->id,
            'status'  => 'ACTIVE',
        ]);

        // Se registra historial del cambio de dueño
        $this->assertDatabaseHas('animal_history', [
            'animal_id'   => $animal->id,
            'change_type' => 'ADOPTION',
        ]);
    }

    public function test_withdrawing_adoption_returns_animal_to_active(): void
    {
        $this->actingAsRole('COORDINATOR');
        $owner   = $this->createUser('CITIZEN');
        $animal  = $this->createAnimalFor($owner, ['status' => 'FOR_ADOPTION']);
        $adoption = Adoption::factory()->create([
            'animal_id' => $animal->id,
            'status'    => 'AVAILABLE',
        ]);

        $this->patchJson("{$this->apiBase}/adoptions/{$adoption->id}/status", [
            'status' => 'WITHDRAWN',
        ])->assertStatus(200);

        $this->assertDatabaseHas('animals', [
            'id'     => $animal->id,
            'status' => 'ACTIVE',
        ]);
    }

    public function test_adopted_status_requires_adopted_by_and_adopted_at(): void
    {
        $this->actingAsRole('COORDINATOR');
        $owner   = $this->createUser('CITIZEN');
        $animal  = $this->createAnimalFor($owner);
        $adoption = Adoption::factory()->create(['animal_id' => $animal->id]);

        $this->patchJson("{$this->apiBase}/adoptions/{$adoption->id}/status", [
            'status' => 'ADOPTED',
            // Sin adopted_by ni adopted_at
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['adopted_by', 'adopted_at']);
    }

    public function test_inspector_cannot_change_adoption_status(): void
    {
        $this->actingAsRole('INSPECTOR');
        $owner   = $this->createUser('CITIZEN');
        $animal  = $this->createAnimalFor($owner);
        $adoption = Adoption::factory()->create(['animal_id' => $animal->id]);

        $this->patchJson("{$this->apiBase}/adoptions/{$adoption->id}/status", [
            'status' => 'IN_PROCESS',
        ])->assertStatus(403);
    }
}