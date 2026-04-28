<?php

namespace Tests\Feature\StrayAnimals;

use Tests\TestCase;
use App\Models\StrayAnimal;

class StrayAnimalTest extends TestCase
{
    // ════════════════════════════════════════════════════════════════════
    // STORE
    // ════════════════════════════════════════════════════════════════════

    public function test_inspector_can_register_stray_animal(): void
    {
        $this->actingAsRole('INSPECTOR');

        $response = $this->postJson("{$this->apiBase}/stray-animals", [
            'approx_gender' => 'UNKNOWN',
            'location'      => 'Av. La Cultura 1200, Cusco',
            'color'         => 'Gris',
            'size'          => 'MEDIUM',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('status', 'OBSERVED');

        $this->assertMatchesRegularExpression(
            '/^SJ-C-\d{4}-\d{6}$/',
            $response->json('code')
        );
    }

    public function test_citizen_cannot_register_stray_animal(): void
    {
        $this->actingAsRole('CITIZEN');

        $this->postJson("{$this->apiBase}/stray-animals", [
            'approx_gender' => 'UNKNOWN',
            'location'      => 'Algún lugar',
        ])->assertStatus(403);
    }

    public function test_store_requires_location_and_gender(): void
    {
        $this->actingAsRole('INSPECTOR');

        $this->postJson("{$this->apiBase}/stray-animals", [])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['location', 'approx_gender']);
    }

    // ════════════════════════════════════════════════════════════════════
    // UPDATE STATUS
    // ════════════════════════════════════════════════════════════════════

    public function test_veterinarian_can_change_stray_status_to_rescued(): void
    {
        $this->actingAsRole('VETERINARIAN');
        $stray = StrayAnimal::factory()->create(['status' => 'OBSERVED']);

        $this->patchJson("{$this->apiBase}/stray-animals/{$stray->id}/status", [
            'status'      => 'RESCUED',
            'description' => 'Rescatado por brigada veterinaria',
        ])->assertStatus(200)
          ->assertJsonPath('status', 'RESCUED');

        $this->assertDatabaseHas('stray_animal_history', [
            'stray_animal_id' => $stray->id,
            'new_status'      => 'RESCUED',
        ]);
    }

    public function test_status_change_always_creates_history(): void
    {
        $this->actingAsRole('INSPECTOR');
        $stray = StrayAnimal::factory()->create(['status' => 'OBSERVED']);

        $this->patchJson("{$this->apiBase}/stray-animals/{$stray->id}/status", [
            'status' => 'RESCUED',
        ]);

        $this->assertDatabaseHas('stray_animal_history', [
            'stray_animal_id' => $stray->id,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════
    // PHOTOS
    // ════════════════════════════════════════════════════════════════════

    public function test_inspector_can_add_photos_to_stray_animal(): void
    {
        $this->actingAsRole('INSPECTOR');
        $stray = StrayAnimal::factory()->create();

        $this->postJson("{$this->apiBase}/stray-animals/{$stray->id}/photos", [
            'urls' => [
                'https://storage.example.com/stray1.jpg',
                'https://storage.example.com/stray2.jpg',
            ],
        ])->assertStatus(201);

        $this->assertDatabaseCount('stray_animal_photos', 2);
    }

    // ════════════════════════════════════════════════════════════════════
    // DESTROY
    // ════════════════════════════════════════════════════════════════════

    public function test_coordinator_can_soft_delete_stray_animal(): void
    {
        $this->actingAsRole('COORDINATOR');
        $stray = StrayAnimal::factory()->create();

        $this->deleteJson("{$this->apiBase}/stray-animals/{$stray->id}")
             ->assertStatus(200);

        $this->assertSoftDeleted('stray_animals', ['id' => $stray->id]);
    }

    public function test_inspector_cannot_delete_stray_animal(): void
    {
        $this->actingAsRole('INSPECTOR');
        $stray = StrayAnimal::factory()->create();

        $this->deleteJson("{$this->apiBase}/stray-animals/{$stray->id}")
             ->assertStatus(403);
    }
}