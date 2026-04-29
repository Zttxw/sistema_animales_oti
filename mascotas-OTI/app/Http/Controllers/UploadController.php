<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    /**
     * Sube un archivo y retorna la URL pública.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:5120', // max 5MB
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('uploads', 'public');
            return response()->json([
                'url' => asset('storage/' . $path),
                'path' => $path
            ], 201);
        }

        return response()->json(['message' => 'No se proporcionó un archivo válido'], 400);
    }
}
