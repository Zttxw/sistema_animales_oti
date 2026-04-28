<?php

namespace App\Http\Controllers;

use App\Models\Species;
use App\Models\Breed;
use App\Models\CampaignType;
use App\Models\ProcedureType;
use App\Models\PostType;
use App\Models\VaccineCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Gestiona todos los catálogos de configuración del sistema.
 * Acceso: ADMIN
 */
class CatalogController extends Controller
{
    // ══════════════════════════════════════════════════════════════════════
    // SPECIES
    // ══════════════════════════════════════════════════════════════════════

    public function speciesIndex()
    {
        return response()->json(Species::with('breeds')->get());
    }

    public function speciesStore(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:50|unique:species']);
        return response()->json(Species::create($data), 201);
    }

    public function speciesUpdate(Request $request, Species $species)
    {
        $data = $request->validate([
            'name'   => ['sometimes','string','max:50', Rule::unique('species')->ignore($species->id)],
            'active' => 'sometimes|boolean',
        ]);
        $species->update($data);
        return response()->json($species->fresh());
    }

    // ══════════════════════════════════════════════════════════════════════
    // BREEDS
    // ══════════════════════════════════════════════════════════════════════

    public function breedsBySpecies(Species $species)
    {
        return response()->json($species->breeds()->active()->get());
    }

    public function breedStore(Request $request)
    {
        $data = $request->validate([
            'species_id' => 'required|exists:species,id',
            'name'       => 'required|string|max:100',
        ]);
        return response()->json(Breed::create($data), 201);
    }

    public function breedUpdate(Request $request, Breed $breed)
    {
        $data = $request->validate([
            'name'   => 'sometimes|string|max:100',
            'active' => 'sometimes|boolean',
        ]);
        $breed->update($data);
        return response()->json($breed->fresh());
    }

    // ══════════════════════════════════════════════════════════════════════
    // CAMPAIGN TYPES
    // ══════════════════════════════════════════════════════════════════════

    public function campaignTypes()
    {
        return response()->json(CampaignType::active()->get());
    }

    public function campaignTypeStore(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:80|unique:campaign_types',
            'description' => 'nullable|string|max:255',
            'icon'        => 'nullable|string|max:50',
        ]);
        return response()->json(CampaignType::create($data), 201);
    }

    // ══════════════════════════════════════════════════════════════════════
    // PROCEDURE TYPES
    // ══════════════════════════════════════════════════════════════════════

    public function procedureTypes()
    {
        return response()->json(ProcedureType::where('active', true)->get());
    }

    public function procedureTypeStore(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:80|unique:procedure_types',
            'description'     => 'nullable|string|max:255',
            'requires_detail' => 'sometimes|boolean',
        ]);
        return response()->json(ProcedureType::create($data), 201);
    }

    // ══════════════════════════════════════════════════════════════════════
    // POST TYPES
    // ══════════════════════════════════════════════════════════════════════

    public function postTypes()
    {
        return response()->json(PostType::where('active', true)->get());
    }

    // ══════════════════════════════════════════════════════════════════════
    // VACCINE CATALOG
    // ══════════════════════════════════════════════════════════════════════

    public function vaccineCatalog(Request $request)
    {
        $vaccines = VaccineCatalog::active()
            ->when($request->species_id, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('species_id', $request->species_id)->orWhereNull('species_id');
            }))
            ->get();

        return response()->json($vaccines);
    }

    public function vaccineCatalogStore(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'species_id'  => 'nullable|exists:species,id',
        ]);
        return response()->json(VaccineCatalog::create($data), 201);
    }
}