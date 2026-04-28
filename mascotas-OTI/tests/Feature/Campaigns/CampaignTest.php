<?php

namespace Tests\Feature\Campaigns;

use Tests\TestCase;
use App\Models\Campaign;
use App\Models\CampaignType;

class CampaignTest extends TestCase
{
    private CampaignType $campaignType;
    private array $validPayload;

    protected function setUp(): void
    {
        parent::setUp();

        $this->campaignType = CampaignType::factory()->create();

        $this->validPayload = [
            'title'            => 'Campaña de Vacunación Antirrábica',
            'campaign_type_id' => $this->campaignType->id,
            'scheduled_at'     => now()->addDays(10)->toDateString(),
            'location'         => 'Plaza de Armas, San Jerónimo',
            'capacity'         => 100,
        ];
    }

    // ════════════════════════════════════════════════════════════════════
    // INDEX (público)
    // ════════════════════════════════════════════════════════════════════

    public function test_anyone_can_list_campaigns(): void
    {
        Campaign::factory()->count(3)->create(['status' => 'PUBLISHED']);

        $this->getJson("{$this->apiBase}/campaigns")
             ->assertStatus(200)
             ->assertJsonStructure(['data']);
    }

    // ════════════════════════════════════════════════════════════════════
    // STORE
    // ════════════════════════════════════════════════════════════════════

    public function test_coordinator_can_create_campaign(): void
    {
        $this->actingAsRole('COORDINATOR');

        $this->postJson("{$this->apiBase}/campaigns", $this->validPayload)
             ->assertStatus(201)
             ->assertJsonPath('title', 'Campaña de Vacunación Antirrábica')
             ->assertJsonPath('status', 'DRAFT');
    }

    public function test_citizen_cannot_create_campaign(): void
    {
        $this->actingAsRole('CITIZEN');

        $this->postJson("{$this->apiBase}/campaigns", $this->validPayload)
             ->assertStatus(403);
    }

    public function test_campaign_creation_fails_with_past_date(): void
    {
        $this->actingAsRole('COORDINATOR');

        $this->postJson("{$this->apiBase}/campaigns", array_merge(
            $this->validPayload,
            ['scheduled_at' => now()->subDay()->toDateString()]
        ))->assertStatus(422)
          ->assertJsonValidationErrors(['scheduled_at']);
    }

    // ════════════════════════════════════════════════════════════════════
    // UPDATE STATUS
    // ════════════════════════════════════════════════════════════════════

    public function test_coordinator_can_publish_campaign(): void
    {
        $coordinator = $this->actingAsRole('COORDINATOR');
        $campaign    = Campaign::factory()->create([
            'created_by' => $coordinator->id,
            'status'     => 'DRAFT',
        ]);

        $this->patchJson("{$this->apiBase}/campaigns/{$campaign->id}/status", [
            'status' => 'PUBLISHED',
        ])->assertStatus(200)
          ->assertJsonPath('status', 'PUBLISHED');
    }

    public function test_cannot_modify_finished_or_cancelled_campaign(): void
    {
        $coordinator = $this->actingAsRole('COORDINATOR');
        $campaign    = Campaign::factory()->create([
            'created_by' => $coordinator->id,
            'status'     => 'FINISHED',
        ]);

        $this->putJson("{$this->apiBase}/campaigns/{$campaign->id}", [
            'title' => 'Intento de edición',
        ])->assertStatus(422);
    }

    // ════════════════════════════════════════════════════════════════════
    // PARTICIPANTES
    // ════════════════════════════════════════════════════════════════════

    public function test_citizen_can_register_to_published_campaign(): void
    {
        $citizen  = $this->actingAsRole('CITIZEN');
        $campaign = Campaign::factory()->create(['status' => 'PUBLISHED', 'capacity' => 50]);
        $animal   = $this->createAnimalFor($citizen);

        $this->postJson("{$this->apiBase}/campaigns/{$campaign->id}/participants", [
            'user_id'   => $citizen->id,
            'animal_id' => $animal->id,
        ])->assertStatus(201);

        $this->assertDatabaseHas('campaign_participants', [
            'campaign_id' => $campaign->id,
            'user_id'     => $citizen->id,
        ]);
    }

    public function test_citizen_cannot_register_to_draft_campaign(): void
    {
        $citizen  = $this->actingAsRole('CITIZEN');
        $campaign = Campaign::factory()->create(['status' => 'DRAFT']);

        $this->postJson("{$this->apiBase}/campaigns/{$campaign->id}/participants", [
            'user_id' => $citizen->id,
        ])->assertStatus(403);
    }

    public function test_registration_fails_when_campaign_is_full(): void
    {
        $coordinator = $this->actingAsRole('COORDINATOR');
        $campaign    = Campaign::factory()->create(['status' => 'PUBLISHED', 'capacity' => 1]);

        // Llenar la campaña
        $existing = $this->createUser('CITIZEN');
        $campaign->participants()->create(['user_id' => $existing->id]);

        $new = $this->createUser('CITIZEN');

        $this->postJson("{$this->apiBase}/campaigns/{$campaign->id}/participants", [
            'user_id' => $new->id,
        ])->assertStatus(422);
    }

    // ════════════════════════════════════════════════════════════════════
    // ASISTENCIA
    // ════════════════════════════════════════════════════════════════════

    public function test_coordinator_can_mark_attendance(): void
    {
        $coordinator = $this->actingAsRole('COORDINATOR');
        $campaign    = Campaign::factory()->create(['status' => 'IN_PROGRESS']);
        $citizen     = $this->createUser('CITIZEN');

        $participant = $campaign->participants()->create([
            'user_id'  => $citizen->id,
            'attended' => false,
        ]);

        $this->patchJson("{$this->apiBase}/campaigns/{$campaign->id}/participants/attendance", [
            'participant_id' => $participant->id,
            'attended'       => true,
        ])->assertStatus(200);

        $this->assertDatabaseHas('campaign_participants', [
            'id'       => $participant->id,
            'attended' => true,
        ]);
    }

    public function test_citizen_cannot_mark_attendance(): void
    {
        $this->actingAsRole('CITIZEN');
        $campaign    = Campaign::factory()->create(['status' => 'IN_PROGRESS']);
        $participant = $campaign->participants()->create(['user_id' => $this->createUser('CITIZEN')->id]);

        $this->patchJson("{$this->apiBase}/campaigns/{$campaign->id}/participants/attendance", [
            'participant_id' => $participant->id,
            'attended'       => true,
        ])->assertStatus(403);
    }
}