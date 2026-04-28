<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Vaccination;
use App\Models\AnimalHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class VaccinationController extends Controller
{
    /**
     * Listar vacunas de un animal.
     */
    public function index(Animal $animal)
    {
        Gate::authorize('viewAny', [Vaccination::class, $animal]);
        $vaccinations = $animal->vaccinations()
            ->with(['vaccine', 'registeredBy', 'campaign'])
            ->orderByDesc('applied_at')
            ->get();

        return response()->json($vaccinations);
    }

    /**
     * Registrar nueva vacuna para un animal.
     */
    public function store(Request $request, Animal $animal)
    {
        Gate::authorize('create', [Vaccination::class, $animal]);
        $data = $request->validate([
            'vaccine_id'   => 'nullable|exists:vaccine_catalog,id',
            'vaccine_name' => 'required|string|max:100',
            'applied_at'   => 'required|date|before_or_equal:today',
            'next_dose_at' => 'nullable|date|after:applied_at',
            'notes'        => 'nullable|string',
            'file_path'    => 'nullable|url|max:500',
            'campaign_id'  => 'nullable|exists:campaigns,id',
        ]);

        $data['animal_id']     = $animal->id;
        $data['registered_by'] = Auth::id();

        $vaccination = Vaccination::create($data);

        AnimalHistory::create([
            'animal_id'   => $animal->id,
            'user_id'     => Auth::id(),
            'change_type' => 'VACCINE',
            'description' => "Vacuna registrada: {$data['vaccine_name']} el {$data['applied_at']}.",
        ]);

        return response()->json($vaccination->load(['vaccine', 'registeredBy']), 201);
    }

    /**
     * Detalle de una vacuna.
     */
    public function show(Animal $animal, Vaccination $vaccination)
    {
        Gate::authorize('view', [$vaccination, $animal]);
        $this->ensureBelongsToAnimal($vaccination, $animal);

        return response()->json($vaccination->load(['vaccine', 'registeredBy', 'campaign']));
    }

    /**
     * Actualizar datos de una vacuna.
     */
    public function update(Request $request, Animal $animal, Vaccination $vaccination)
    {
        Gate::authorize('update', [$vaccination, $animal]);
        $this->ensureBelongsToAnimal($vaccination, $animal);

        $data = $request->validate([
            'vaccine_name' => 'sometimes|string|max:100',
            'applied_at'   => 'sometimes|date|before_or_equal:today',
            'next_dose_at' => 'nullable|date',
            'notes'        => 'nullable|string',
            'file_path'    => 'nullable|url|max:500',
        ]);

        $vaccination->update($data);

        return response()->json($vaccination->fresh(['vaccine', 'registeredBy']));
    }

    /**
     * Eliminar una vacuna.
     */
    public function destroy(Animal $animal, Vaccination $vaccination)
    {
        Gate::authorize('delete', [$vaccination, $animal]);
        $this->ensureBelongsToAnimal($vaccination, $animal);

        $vaccination->delete();

        return response()->json(['message' => 'Vacuna eliminada correctamente.']);
    }

    /**
     * Listar vacunas próximas a vencer (próximos N días).
     */
    public function upcoming(Request $request)
    {
        Gate::authorize('viewUpcoming', Vaccination::class);
        $days = $request->integer('days', 30);

        $vaccinations = Vaccination::with(['animal.owner', 'vaccine'])
            ->whereNotNull('next_dose_at')
            ->whereBetween('next_dose_at', [today(), today()->addDays($days)])
            ->orderBy('next_dose_at')
            ->get();

        return response()->json($vaccinations);
    }

    // ── Private ────────────────────────────────────────────────────────────

    private function ensureBelongsToAnimal(Vaccination $vaccination, Animal $animal): void
    {
        if ($vaccination->animal_id !== $animal->id) {
            abort(404, 'Vacuna no pertenece a este animal.');
        }
    }
}