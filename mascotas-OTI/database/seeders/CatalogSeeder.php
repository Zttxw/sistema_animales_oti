<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Species;
use App\Models\Breed;
use App\Models\CampaignType;
use App\Models\ProcedureType;
use App\Models\PostType;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // ── Species ──────────────────────────────────────────────
        $canino = Species::firstOrCreate(['name' => 'Canino']);
        $felino = Species::firstOrCreate(['name' => 'Felino']);

        // ── Breeds ───────────────────────────────────────────────
        $dogBreeds = ['Mestizo', 'Labrador', 'Pastor Alemán', 'Bulldog', 'Poodle', 'Chihuahua'];
        $catBreeds = ['Mestizo', 'Persa', 'Siamés', 'Angora', 'Maine Coon'];

        foreach ($dogBreeds as $b) {
            Breed::firstOrCreate(['species_id' => $canino->id, 'name' => $b]);
        }
        foreach ($catBreeds as $b) {
            Breed::firstOrCreate(['species_id' => $felino->id, 'name' => $b]);
        }

        // ── Campaign Types ───────────────────────────────────────
        $campaignTypes = [
            ['name' => 'Vacunación',       'description' => 'Campaña de vacunación masiva'],
            ['name' => 'Esterilización',   'description' => 'Campaña de esterilización'],
            ['name' => 'Desparasitación',  'description' => 'Campaña de desparasitación'],
            ['name' => 'Adopción',         'description' => 'Feria de adopción'],
            ['name' => 'Censo',            'description' => 'Censo de animales en la zona'],
        ];
        foreach ($campaignTypes as $ct) {
            CampaignType::firstOrCreate(['name' => $ct['name']], $ct);
        }

        // ── Procedure Types ─────────────────────────────────────
        $procedureTypes = [
            ['name' => 'Esterilización',    'description' => 'Cirugía de esterilización', 'requires_detail' => false],
            ['name' => 'Desparasitación',   'description' => 'Aplicación de antiparasitario', 'requires_detail' => false],
            ['name' => 'Cirugía',           'description' => 'Procedimiento quirúrgico',  'requires_detail' => true],
            ['name' => 'Consulta',          'description' => 'Consulta veterinaria',       'requires_detail' => true],
            ['name' => 'Emergencia',        'description' => 'Atención de emergencia',     'requires_detail' => true],
        ];
        foreach ($procedureTypes as $pt) {
            ProcedureType::firstOrCreate(['name' => $pt['name']], $pt);
        }

        // ── Post Types ──────────────────────────────────────────
        $postTypes = [
            ['name' => 'Noticia',           'description' => 'Noticias generales del sistema'],
            ['name' => 'Aviso de Pérdida',  'description' => 'Reporte de mascota perdida'],
            ['name' => 'Evento',            'description' => 'Evento o campaña próxima'],
            ['name' => 'Alerta',            'description' => 'Alerta sanitaria o de seguridad'],
        ];
        foreach ($postTypes as $pt) {
            PostType::firstOrCreate(['name' => $pt['name']], $pt);
        }
    }
}
