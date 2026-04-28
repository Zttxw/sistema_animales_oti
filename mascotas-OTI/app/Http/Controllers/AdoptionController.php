<?php

namespace App\Http\Controllers;

use App\Models\Adoption;
use App\Models\Animal;
use App\Models\AnimalHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;

class AdoptionController extends Controller
{
    public function index(Request $request)
    {
        $adoptions = Adoption::with(['animal.species', 'animal.breed', 'adopter', 'reviewer'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json($adoptions);
    }

    /**
     * Publicar un animal para adopción.
     * El animal debe existir y estar en estado ACTIVE o FOR_ADOPTION.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Adoption::class);
        $data = $request->validate([
            'animal_id'    => 'required|exists:animals,id|unique:adoptions,animal_id',
            'reason'       => 'nullable|string',
            'description'  => 'nullable|string',
            'requirements' => 'nullable|string',
            'contact'      => 'nullable|string|max:150',
        ]);

        DB::transaction(function () use ($data) {

            $animal = Animal::findOrFail($data['animal_id']);

            Adoption::create(array_merge($data, ['status' => 'AVAILABLE']));

            $animal->update(['status' => 'FOR_ADOPTION']);

            AnimalHistory::create([
                'animal_id'   => $animal->id,
                'user_id'     => Auth::id(),
                'change_type' => 'ADOPTION',
                'description' => 'Animal puesto en adopción.',
            ]);
        });

        $adoption = Adoption::with(['animal.species', 'animal.breed'])
            ->where('animal_id', $data['animal_id'])
            ->first();

        return response()->json($adoption, 201);
    }

    public function show(Adoption $adoption)
    {
        return response()->json(
            $adoption->load(['animal.species', 'animal.breed', 'animal.photos', 'adopter', 'reviewer'])
        );
    }

    public function update(Request $request, Adoption $adoption)
    {
        Gate::authorize('update', $adoption);

        $data = $request->validate([
            'reason'       => 'nullable|string',
            'description'  => 'nullable|string',
            'requirements' => 'nullable|string',
            'contact'      => 'nullable|string|max:150',
        ]);

        $adoption->update($data);
        return response()->json($adoption->fresh());
    }

    /**
     * Cambiar estado de adopción (ADMIN/VETERINARIAN).
     */
    public function updateStatus(Request $request, Adoption $adoption)
    {
        Gate::authorize('updateStatus', $adoption);
        $request->validate([
            'status'      => ['required', Rule::in(['AVAILABLE','IN_PROCESS','ADOPTED','WITHDRAWN'])],
            'admin_notes' => 'nullable|string',
            'adopted_by'  => 'required_if:status,ADOPTED|nullable|exists:users,id',
            'adopted_at'  => 'required_if:status,ADOPTED|nullable|date',
        ]);

        DB::transaction(function () use ($request, $adoption) {
            $adoption->update([
                'status'      => $request->status,
                'admin_notes' => $request->admin_notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'adopted_by'  => $request->adopted_by,
                'adopted_at'  => $request->adopted_at,
            ]);

            // Si se concreta la adopción, cambiar propietario del animal
            if ($request->status === 'ADOPTED' && $request->adopted_by) {
                $animal = $adoption->animal;
                $previous_owner = $animal->user_id;

                $animal->update([
                    'user_id' => $request->adopted_by,
                    'status'  => 'ACTIVE',
                ]);

                AnimalHistory::create([
                    'animal_id'     => $animal->id,
                    'user_id'       => Auth::id(),
                    'change_type'   => 'ADOPTION',
                    'description'   => "Adopción completada. Nuevo propietario ID: {$request->adopted_by}.",
                    'previous_data' => ['previous_owner_id' => $previous_owner],
                ]);
            }

            // Si se retira, regresar el animal a ACTIVE
            if ($request->status === 'WITHDRAWN') {
                $adoption->animal->update(['status' => 'ACTIVE']);
            }
        });

        return response()->json($adoption->fresh(['animal', 'adopter', 'reviewer']));
    }
}