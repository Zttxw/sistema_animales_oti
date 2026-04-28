<?php

namespace App\Http\Controllers;

use App\Models\StrayAnimal;
use App\Models\StrayAnimalHistory;
use App\Models\StrayAnimalPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;

class StrayAnimalController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', StrayAnimal::class);
        $strays = StrayAnimal::with(['species', 'breed', 'reporter', 'photos'])
            ->when($request->status,     fn($q) => $q->where('status',     $request->status))
            ->when($request->species_id, fn($q) => $q->where('species_id', $request->species_id))
            ->when($request->search,     fn($q) => $q->where('location', 'like', "%{$request->search}%")
                                                      ->orWhere('code',   'like', "%{$request->search}%"))
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json($strays);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', StrayAnimal::class);
        $data = $request->validate([
            'species_id'   => 'nullable|exists:species,id',
            'breed_id'     => 'nullable|exists:breeds,id',
            'approx_gender'=> ['required', Rule::in(['M','F','UNKNOWN'])],
            'color'        => 'nullable|string|max:100',
            'size'         => ['nullable', Rule::in(['SMALL','MEDIUM','LARGE','GIANT'])],
            'location'     => 'required|string',
            'notes'        => 'nullable|string',
        ]);

        $data['code']        = $this->generateCode();
        $data['status']      = 'OBSERVED';
        $data['reported_by'] = Auth::id();

        $stray = StrayAnimal::create($data);

        StrayAnimalHistory::create([
            'stray_animal_id' => $stray->id,
            'new_status'      => 'OBSERVED',
            'description'     => 'Animal registrado en el sistema.',
            'registered_by'   => Auth::id(),
        ]);

        return response()->json($stray->load(['species', 'breed']), 201);
    }

    public function show(StrayAnimal $strayAnimal)
    {
        Gate::authorize('view', $strayAnimal);
        return response()->json(
            $strayAnimal->load(['species', 'breed', 'reporter', 'photos', 'history.registeredBy'])
        );
    }

    public function update(Request $request, StrayAnimal $strayAnimal)
    {
        Gate::authorize('update', $strayAnimal);
        $data = $request->validate([
            'species_id'    => 'nullable|exists:species,id',
            'breed_id'      => 'nullable|exists:breeds,id',
            'approx_gender' => ['sometimes', Rule::in(['M','F','UNKNOWN'])],
            'color'         => 'nullable|string|max:100',
            'size'          => ['nullable', Rule::in(['SMALL','MEDIUM','LARGE','GIANT'])],
            'location'      => 'sometimes|string',
            'notes'         => 'nullable|string',
        ]);

        $strayAnimal->update($data);
        return response()->json($strayAnimal->fresh(['species', 'breed']));
    }

    /**
     * Cambiar estado del animal callejero y registrar historial.
     */
    public function updateStatus(Request $request, StrayAnimal $strayAnimal)
    {
        Gate::authorize('updateStatus', $strayAnimal);
        $validStatuses = ['OBSERVED','RESCUED','IN_TREATMENT','FOR_ADOPTION','DECEASED','RELEASED'];

        $request->validate([
            'status'      => ['required', Rule::in($validStatuses)],
            'description' => 'nullable|string',
        ]);

        $strayAnimal->update(['status' => $request->status]);

        StrayAnimalHistory::create([
            'stray_animal_id' => $strayAnimal->id,
            'new_status'      => $request->status,
            'description'     => $request->description,
            'registered_by'   => Auth::id(),
        ]);

        return response()->json($strayAnimal->fresh());
    }

    public function addPhotos(Request $request, StrayAnimal $strayAnimal)
    {
        Gate::authorize('update', $strayAnimal);
        $request->validate([
            'urls'   => 'required|array|min:1',
            'urls.*' => 'required|url|max:500',
        ]);

        $photos = collect($request->urls)->map(fn($url) => [
            'stray_animal_id' => $strayAnimal->id,
            'path'            => $url,
        ]);

        StrayAnimalPhoto::insert($photos->toArray());

        return response()->json($strayAnimal->photos()->get(), 201);
    }

    public function destroy(StrayAnimal $strayAnimal)
    {
        Gate::authorize('delete', $strayAnimal);
        $strayAnimal->delete(); // SoftDelete
        return response()->json(['message' => 'Registro eliminado correctamente.']);
    }

    // ── Private ────────────────────────────────────────────────────────────

    private function generateCode(): string
    {
        $year = now()->year;
        $last = StrayAnimal::withTrashed()->whereYear('created_at', $year)->count();
        $seq  = str_pad($last + 1, 6, '0', STR_PAD_LEFT);
        return "SJ-C-{$year}-{$seq}";
    }
}