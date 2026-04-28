<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\AnimalHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AnimalController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Animal::class);
        $animals = Animal::with(['species', 'breed', 'coverPhoto'])
            ->when($request->user_id,   fn($q) => $q->where('user_id',   $request->user_id))
            ->when($request->species_id, fn($q) => $q->where('species_id', $request->species_id))
            ->when($request->status,    fn($q) => $q->where('status',    $request->status))
            ->when($request->search,    fn($q) => $q->where('name', 'like', "%{$request->search}%")
                                                     ->orWhere('municipal_code', 'like', "%{$request->search}%"))
            ->paginate(15);

        return response()->json($animals);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Animal::class);
        $data = $request->validate([
            'user_id'              => 'required|exists:users,id',
            'species_id'           => 'required|exists:species,id',
            'breed_id'             => 'nullable|exists:breeds,id',
            'name'                 => 'required|string|max:100',
            'gender'               => ['required', Rule::in(['M', 'F', 'UNKNOWN'])],
            'birth_date'           => 'nullable|date',
            'approximate_age'      => 'nullable|string|max:30',
            'color'                => 'nullable|string|max:100',
            'size'                 => ['nullable', Rule::in(['SMALL','MEDIUM','LARGE','GIANT'])],
            'reproductive_status'  => ['nullable', Rule::in(['INTACT','SPAYED','NEUTERED','UNKNOWN'])],
            'distinctive_features' => 'nullable|string',
            'notes'                => 'nullable|string',
        ]);

        $data['municipal_code'] = $this->generateMunicipalCode();
        $data['status'] = 'ACTIVE';

        $animal = Animal::create($data);

        AnimalHistory::create([
            'animal_id'   => $animal->id,
            'user_id'     => Auth::id(),
            'change_type' => 'DATA',
            'description' => 'Animal registrado en el sistema.',
        ]);

        return response()->json($animal->load(['species', 'breed']), 201);
    }

    public function show(Animal $animal)
    {
        Gate::authorize('view', $animal);
        return response()->json(
            $animal->load(['species', 'breed', 'owner', 'photos', 'vaccinations', 'healthProcedures', 'adoption'])
        );
    }

    public function update(Request $request, Animal $animal)
    {
        Gate::authorize('update', $animal);
        $data = $request->validate([
            'name'                 => 'sometimes|string|max:100',
            'breed_id'             => 'nullable|exists:breeds,id',
            'gender'               => ['sometimes', Rule::in(['M','F','UNKNOWN'])],
            'birth_date'           => 'nullable|date',
            'approximate_age'      => 'nullable|string|max:30',
            'color'                => 'nullable|string|max:100',
            'size'                 => ['nullable', Rule::in(['SMALL','MEDIUM','LARGE','GIANT'])],
            'reproductive_status'  => ['nullable', Rule::in(['INTACT','SPAYED','NEUTERED','UNKNOWN'])],
            'distinctive_features' => 'nullable|string',
            'notes'                => 'nullable|string',
        ]);

        $previous = $animal->only(array_keys($data));
        $animal->update($data);

        AnimalHistory::create([
            'animal_id'     => $animal->id,
            'user_id'       => Auth::id(),
            'change_type'   => 'DATA',
            'description'   => 'Datos del animal actualizados.',
            'previous_data' => $previous,
        ]);

        return response()->json($animal->fresh(['species', 'breed']));
    }

    public function updateStatus(Request $request, Animal $animal)
    {
        Gate::authorize('updateStatus', $animal);
        $request->validate([
            'status'       => ['required', Rule::in(['ACTIVE','LOST','FOR_ADOPTION','DECEASED'])],
            'death_date'   => 'required_if:status,DECEASED|nullable|date',
            'death_reason' => 'nullable|string',
        ]);

        $previous = $animal->status;

        $animal->update([
            'status'       => $request->status,
            'death_date'   => $request->death_date,
            'death_reason' => $request->death_reason,
        ]);

        AnimalHistory::create([
            'animal_id'     => $animal->id,
            'user_id'       => Auth::id(),
            'change_type'   => 'STATUS',
            'description'   => "Estado cambiado de {$previous} a {$request->status}.",
            'previous_data' => ['status' => $previous],
        ]);

        return response()->json($animal->fresh());
    }

    public function destroy(Animal $animal)
    {
        Gate::authorize('delete', $animal);
        $animal->delete(); // SoftDelete
        return response()->json(['message' => 'Animal eliminado correctamente.']);
    }

    public function history(Animal $animal)
    {
        Gate::authorize('viewHistory', $animal);
        return response()->json(
            $animal->history()->with('registeredBy')->latest()->get()
        );
    }

    // ── Private helpers ────────────────────────────────────────────────────

    private function generateMunicipalCode(): string
    {
        $year    = now()->year;
        $last    = Animal::withTrashed()->whereYear('created_at', $year)->count();
        $seq     = str_pad($last + 1, 6, '0', STR_PAD_LEFT);
        return "SJ-{$year}-{$seq}";
    }
}