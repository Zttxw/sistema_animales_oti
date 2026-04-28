<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\AnimalPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnimalPhotoController extends Controller
{
    public function store(Request $request, Animal $animal)
    {
        $request->validate([
            'urls'      => 'required|array|min:1|max:10',
            'urls.*'    => 'required|url|max:500',
            'cover_index' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $animal) {
            // Si se marca cover, limpiar el actual
            if ($request->has('cover_index')) {
                $animal->photos()->update(['is_cover' => false]);
            }

            foreach ($request->urls as $index => $url) {
                AnimalPhoto::create([
                    'animal_id' => $animal->id,
                    'url'       => $url,
                    'is_cover'  => $request->cover_index === $index,
                ]);
            }
        });

        return response()->json($animal->photos()->get(), 201);
    }

    public function setCover(Animal $animal, AnimalPhoto $photo)
    {
        if ($photo->animal_id !== $animal->id) {
            abort(404);
        }

        DB::transaction(function () use ($animal, $photo) {
            $animal->photos()->update(['is_cover' => false]);
            $photo->update(['is_cover' => true]);
        });

        return response()->json(['message' => 'Foto de portada actualizada.']);
    }

    public function destroy(Animal $animal, AnimalPhoto $photo)
    {
        if ($photo->animal_id !== $animal->id) {
            abort(404);
        }

        $photo->delete();
        return response()->json(['message' => 'Foto eliminada.']);
    }
}