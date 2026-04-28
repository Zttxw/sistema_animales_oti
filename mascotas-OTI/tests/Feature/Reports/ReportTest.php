<?php

namespace Tests\Feature\Reports;

use Tests\TestCase;

class ReportTest extends TestCase
{
    public function test_admin_can_access_dashboard(): void
    {
        $this->actingAsRole('ADMIN');

        $this->getJson("{$this->apiBase}/reports/dashboard")
             ->assertStatus(200)
             ->assertJsonStructure([
                 'animals'              => ['total', 'active', 'lost', 'for_adoption', 'deceased'],
                 'stray_animals'        => ['total', 'observed', 'rescued'],
                 'adoptions'            => ['available', 'in_process', 'completed'],
                 'campaigns'            => ['total', 'upcoming', 'finished'],
                 'vaccinations_pending',
                 'users_total',
             ]);
    }

    public function test_veterinarian_can_access_reports(): void
    {
        $this->actingAsRole('VETERINARIAN');

        $this->getJson("{$this->apiBase}/reports/dashboard")
             ->assertStatus(200);
    }

    public function test_citizen_cannot_access_reports(): void
    {
        $this->actingAsRole('CITIZEN');

        $this->getJson("{$this->apiBase}/reports/dashboard")
             ->assertStatus(403);
    }

    public function test_animals_by_species_returns_grouped_data(): void
    {
        $this->actingAsRole('ADMIN');

        $this->getJson("{$this->apiBase}/reports/animals/by-species")
             ->assertStatus(200)
             ->assertJsonIsArray();
    }

    public function test_animals_per_month_returns_last_12_months(): void
    {
        $this->actingAsRole('ADMIN');

        $this->getJson("{$this->apiBase}/reports/animals/per-month")
             ->assertStatus(200)
             ->assertJsonIsArray();
    }

    public function test_stray_animals_summary_returns_by_status_and_species(): void
    {
        $this->actingAsRole('ADMIN');

        $this->getJson("{$this->apiBase}/reports/stray-animals/summary")
             ->assertStatus(200)
             ->assertJsonStructure(['by_status', 'by_species']);
    }
}