<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\HealthProcedure;
use App\Models\AnimalHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HealthProcedureController extends Controller
{
    public function index(Animal $animal)
    {
        $procedures = $animal->healthProcedures()
            ->with(['procedureType', 'registeredBy', 'campaign'])
            ->orderByDesc('performed_at')
            ->get();

        return response()->json($procedures);
    }

    public function store(Request $request, Animal $animal)
    {
        $data = $request->validate([
            'procedure_type_id' => 'required|exists:procedure_types,id',
            'type_detail'       => 'nullable|string|max:100',
            'performed_at'      => 'required|date|before_or_equal:today',
            'description'       => 'nullable|string',
            'notes'             => 'nullable|string',
            'file_url'          => 'nullable|url|max:500',
            'campaign_id'       => 'nullable|exists:campaigns,id',
        ]);

        $data['animal_id']     = $animal->id;
        $data['registered_by'] = Auth::id();

        $procedure = HealthProcedure::create($data);

        AnimalHistory::create([
            'animal_id'   => $animal->id,
            'user_id'     => Auth::id(),
            'change_type' => 'PROCEDURE',
            'description' => "Procedimiento registrado el {$data['performed_at']}.",
        ]);

        return response()->json($procedure->load(['procedureType', 'registeredBy']), 201);
    }

    public function show(Animal $animal, HealthProcedure $healthProcedure)
    {
        $this->ensureBelongs($healthProcedure, $animal);
        return response()->json($healthProcedure->load(['procedureType', 'registeredBy', 'campaign']));
    }

    public function update(Request $request, Animal $animal, HealthProcedure $healthProcedure)
    {
        $this->ensureBelongs($healthProcedure, $animal);

        $data = $request->validate([
            'type_detail'  => 'nullable|string|max:100',
            'performed_at' => 'sometimes|date|before_or_equal:today',
            'description'  => 'nullable|string',
            'notes'        => 'nullable|string',
            'file_url'     => 'nullable|url|max:500',
        ]);

        $healthProcedure->update($data);
        return response()->json($healthProcedure->fresh('procedureType'));
    }

    public function destroy(Animal $animal, HealthProcedure $healthProcedure)
    {
        $this->ensureBelongs($healthProcedure, $animal);
        $healthProcedure->delete();
        return response()->json(['message' => 'Procedimiento eliminado.']);
    }

    private function ensureBelongs(HealthProcedure $procedure, Animal $animal): void
    {
        if ($procedure->animal_id !== $animal->id) {
            abort(404, 'Procedimiento no pertenece a este animal.');
        }
    }
}