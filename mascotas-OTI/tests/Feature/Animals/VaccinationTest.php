<?php

namespace Tests\Feature\Animals;

use Tests\TestCase;


class VaccinationTest extends TestCase
{
    // ════════════════════════════════════════════════════════════════════
    // STORE
    // ════════════════════════════════════════════════════════════════════

    public function test_veterinarian_can_register_vaccination(): void
    {
        $vet    = $this->actingAsRole('VETERINARIAN');
        $owner  = $this->createUser('CITIZEN');
        $animal = $this->createAnimalFor($owner);

        $response = $this->postJson(
            "{$this->apiBase}/animals/{$animal->id}/vaccinations",
            [
                'vaccine_name' => 'Rabia',
                'applied_at'   => now()->toDateString(),
                'next_dose_at' => now()->addYear()->toDateString(),
            ]
        );

        $response->assertStatus(201)
                 ->assertJsonPath('vaccine_name', 'Rabia');

        $this->assertDatabaseHas('vaccinations', [
            'animal_id'    => $animal->id,
            'vaccine_name' => 'Rabia',
        ]);
    }

    public function test_citizen_cannot_register_vaccination(): void
    {
        $owner  = $this->actingAsRole('CITIZEN');
        $animal = $this->createAnimalFor($owner);

        $this->postJson(
            "{$this->apiBase}/animals/{$animal->id}/vaccinations",
            ['vaccine_name' => 'Rabia', 'applied_at' => now()->toDateString()]
        )->assertStatus(403);
    }

    public function test_vaccination_fails_when_applied_at_is_in_the_future(): void
    {
        $this->actingAsRole('VETERINARIAN');
        $owner  = $this->createUser('CITIZEN');
        $animal = $this->createAnimalFor($owner);

        $this->postJson(
            "{$this->apiBase}/animals/{$animal->id}/vaccinations",
            [
                'vaccine_name' => 'Rabia',
                'applied_at'   => now()->addDays(5)->toDateString(),
            ]
        )->assertStatus(422)
         ->assertJsonValidationErrors(['applied_at']);
    }

    public function test_vaccination_registers_animal_history(): void
    {
        $this->actingAsRole('VETERINARIAN');
        $owner  = $this->createUser('CITIZEN');
        $animal = $this->createAnimalFor($owner);

        $this->postJson(
            "{$this->apiBase}/animals/{$animal->id}/vaccinations",
            ['vaccine_name' => 'Moquillo', 'applied_at' => now()->toDateString()]
        );

        $this->assertDatabaseHas('animal_history', [
            'animal_id'   => $animal->id,
            'change_type' => 'VACCINE',
        ]);
    }

    // ════════════════════════════════════════════════════════════════════
    // INDEX
    // ════════════════════════════════════════════════════════════════════

    public function test_owner_can_list_own_animal_vaccinations(): void
    {
        $owner  = $this->actingAsRole('CITIZEN');
        $animal = $this->createAnimalFor($owner);

        $this->getJson("{$this->apiBase}/animals/{$animal->id}/vaccinations")
             ->assertStatus(200);
    }

    public function test_citizen_cannot_list_another_animals_vaccinations(): void
    {
        $owner  = $this->createUser('CITIZEN');
        $animal = $this->createAnimalFor($owner);

        $this->actingAsRole('CITIZEN');

        $this->getJson("{$this->apiBase}/animals/{$animal->id}/vaccinations")
             ->assertStatus(403);
    }

    // ════════════════════════════════════════════════════════════════════
    // UPCOMING (global)
    // ════════════════════════════════════════════════════════════════════

    public function test_veterinarian_can_see_upcoming_vaccinations(): void
    {
        $this->actingAsRole('VETERINARIAN');

        $this->getJson("{$this->apiBase}/vaccinations/upcoming")
             ->assertStatus(200);
    }

    public function test_citizen_cannot_see_upcoming_vaccinations(): void
    {
        $this->actingAsRole('CITIZEN');

        $this->getJson("{$this->apiBase}/vaccinations/upcoming")
             ->assertStatus(403);
    }
}