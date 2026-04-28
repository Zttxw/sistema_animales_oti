<?php

namespace Tests\Feature\Animals;

use Tests\TestCase;

use App\Models\AnimalHistory;
use App\Models\Species;
use App\Models\Breed;

class AnimalTest extends TestCase
{
    private array $validPayload;
    private Species $species;
    private Breed $breed;

    protected function setUp(): void
    {
        parent::setUp();

        $this->species = Species::factory()->create();
        $this->breed   = Breed::factory()->create(['species_id' => $this->species->id]);

        $this->validPayload = [
            'species_id' => $this->species->id,
            'breed_id'   => $this->breed->id,
            'name'       => 'Firulais',
            'gender'     => 'M',
            'color'      => 'Cafe',
            'size'       => 'MEDIUM',
        ];
    }

    // ════════════════════════════════════════════════════════════════════
    // INDEX
    // ════════════════════════════════════════════════════════════════════

    public function test_authenticated_user_can_list_animals(): void
    {
        $owner = $this->actingAsRole('CITIZEN');
        $this->createAnimalFor($owner);
        $this->createAnimalFor($owner);

        $this->getJson("{$this->apiBase}/animals")
             ->assertStatus(200)
             ->assertJsonStructure(['data', 'current_page', 'last_page']);
    }

    public function test_unauthenticated_user_cannot_list_animals(): void
    {
        $this->getJson("{$this->apiBase}/animals")
             ->assertStatus(401);
    }

    // ════════════════════════════════════════════════════════════════════
    // STORE
    // ════════════════════════════════════════════════════════════════════

    public function test_citizen_can_register_animal(): void
    {
        $citizen = $this->actingAsRole('CITIZEN');

        $response = $this->postJson("{$this->apiBase}/animals", array_merge(
            $this->validPayload,
            ['user_id' => $citizen->id]
        ));

        $response->assertStatus(201)
                 ->assertJsonPath('name', 'Firulais')
                 ->assertJsonPath('status', 'ACTIVE');

        $this->assertDatabaseHas('animals', ['name' => 'Firulais']);
    }

    public function test_animal_gets_auto_generated_municipal_code_on_creation(): void
    {
        $citizen = $this->actingAsRole('CITIZEN');

        $response = $this->postJson("{$this->apiBase}/animals", array_merge(
            $this->validPayload,
            ['user_id' => $citizen->id]
        ));

        $response->assertStatus(201);
        $this->assertNotNull($response->json('municipal_code'));
        $this->assertMatchesRegularExpression('/^SJ-\d{4}-\d{6}$/', $response->json('municipal_code'));
    }

    public function test_animal_creation_generates_history_record(): void
    {
        $citizen = $this->actingAsRole('CITIZEN');

        $response = $this->postJson("{$this->apiBase}/animals", array_merge(
            $this->validPayload,
            ['user_id' => $citizen->id]
        ));

        $animalId = $response->json('id');

        $this->assertDatabaseHas('animal_history', [
            'animal_id'   => $animalId,
            'change_type' => 'DATA',
        ]);
    }

    public function test_animal_store_fails_without_required_fields(): void
    {
        $this->actingAsRole('CITIZEN');

        $this->postJson("{$this->apiBase}/animals", [])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['name', 'gender', 'species_id']);
    }

    public function test_animal_store_fails_with_invalid_gender(): void
    {
        $citizen = $this->actingAsRole('CITIZEN');

        $this->postJson("{$this->apiBase}/animals", array_merge(
            $this->validPayload,
            ['user_id' => $citizen->id, 'gender' => 'X']
        ))->assertStatus(422)
          ->assertJsonValidationErrors(['gender']);
    }

    // ════════════════════════════════════════════════════════════════════
    // SHOW
    // ════════════════════════════════════════════════════════════════════

    public function test_owner_can_see_own_animal(): void
    {
        $owner  = $this->actingAsRole('CITIZEN');
        $animal = $this->createAnimalFor($owner);

        $this->getJson("{$this->apiBase}/animals/{$animal->id}")
             ->assertStatus(200)
             ->assertJsonPath('id', $animal->id);
    }

    public function test_citizen_cannot_see_another_users_animal(): void
    {
        $owner   = $this->createUser('CITIZEN');
        $animal  = $this->createAnimalFor($owner);

        $this->actingAsRole('CITIZEN'); // otro ciudadano

        $this->getJson("{$this->apiBase}/animals/{$animal->id}")
             ->assertStatus(403);
    }

    public function test_veterinarian_can_see_any_animal(): void
    {
        $owner  = $this->createUser('CITIZEN');
        $animal = $this->createAnimalFor($owner);

        $this->actingAsRole('VETERINARIAN');

        $this->getJson("{$this->apiBase}/animals/{$animal->id}")
             ->assertStatus(200);
    }

    // ════════════════════════════════════════════════════════════════════
    // UPDATE
    // ════════════════════════════════════════════════════════════════════

    public function test_owner_can_update_own_animal(): void
    {
        $owner  = $this->actingAsRole('CITIZEN');
        $animal = $this->createAnimalFor($owner);

        $this->putJson("{$this->apiBase}/animals/{$animal->id}", ['name' => 'Nuevo Nombre'])
             ->assertStatus(200)
             ->assertJsonPath('name', 'Nuevo Nombre');
    }

    public function test_citizen_cannot_update_another_users_animal(): void
    {
        $owner  = $this->createUser('CITIZEN');
        $animal = $this->createAnimalFor($owner);

        $this->actingAsRole('CITIZEN');

        $this->putJson("{$this->apiBase}/animals/{$animal->id}", ['name' => 'Hack'])
             ->assertStatus(403);
    }

    public function test_update_generates_history_record(): void
    {
        $owner  = $this->actingAsRole('CITIZEN');
        $animal = $this->createAnimalFor($owner);

        $this->putJson("{$this->apiBase}/animals/{$animal->id}", ['name' => 'Nuevo Nombre']);

        $this->assertDatabaseHas('animal_history', [
            'animal_id'   => $animal->id,
            'change_type' => 'DATA',
        ]);
    }

    // ════════════════════════════════════════════════════════════════════
    // UPDATE STATUS
    // ════════════════════════════════════════════════════════════════════

    public function test_owner_can_report_own_animal_as_lost(): void
    {
        $owner  = $this->actingAsRole('CITIZEN');
        $animal = $this->createAnimalFor($owner);

        $this->patchJson("{$this->apiBase}/animals/{$animal->id}/status", ['status' => 'LOST'])
             ->assertStatus(200)
             ->assertJsonPath('status', 'LOST');
    }

    public function test_citizen_cannot_mark_another_animals_status(): void
    {
        $owner  = $this->createUser('CITIZEN');
        $animal = $this->createAnimalFor($owner);

        $this->actingAsRole('CITIZEN');

        $this->patchJson("{$this->apiBase}/animals/{$animal->id}/status", ['status' => 'LOST'])
             ->assertStatus(403);
    }

    public function test_status_update_requires_death_info_when_deceased(): void
    {
        $vet    = $this->actingAsRole('VETERINARIAN');
        $owner  = $this->createUser('CITIZEN');
        $animal = $this->createAnimalFor($owner);

        // Sin death_date → debe fallar
        $this->patchJson("{$this->apiBase}/animals/{$animal->id}/status", [
            'status' => 'DECEASED',
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['death_date']);
    }

    public function test_status_change_generates_history_record(): void
    {
        $owner  = $this->actingAsRole('CITIZEN');
        $animal = $this->createAnimalFor($owner);

        $this->patchJson("{$this->apiBase}/animals/{$animal->id}/status", ['status' => 'LOST']);

        $this->assertDatabaseHas('animal_history', [
            'animal_id'   => $animal->id,
            'change_type' => 'STATUS',
        ]);
    }

    // ════════════════════════════════════════════════════════════════════
    // DELETE
    // ════════════════════════════════════════════════════════════════════

    public function test_admin_can_soft_delete_animal(): void
    {
        $this->actingAsRole('ADMIN');
        $owner  = $this->createUser('CITIZEN');
        $animal = $this->createAnimalFor($owner);

        $this->deleteJson("{$this->apiBase}/animals/{$animal->id}")
             ->assertStatus(200);

        $this->assertSoftDeleted('animals', ['id' => $animal->id]);
    }

    public function test_citizen_cannot_delete_another_users_animal(): void
    {
        $owner  = $this->createUser('CITIZEN');
        $animal = $this->createAnimalFor($owner);

        $this->actingAsRole('CITIZEN');

        $this->deleteJson("{$this->apiBase}/animals/{$animal->id}")
             ->assertStatus(403);
    }

    // ════════════════════════════════════════════════════════════════════
    // HISTORY
    // ════════════════════════════════════════════════════════════════════

    public function test_owner_can_view_animal_history(): void
    {
        $owner  = $this->actingAsRole('CITIZEN');
        $animal = $this->createAnimalFor($owner);

        AnimalHistory::factory()->create([
            'animal_id'   => $animal->id,
            'change_type' => 'DATA',
        ]);

        $this->getJson("{$this->apiBase}/animals/{$animal->id}/history")
             ->assertStatus(200)
             ->assertJsonIsArray();
    }
}