<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MateriaController extends Controller
{
    // ─── GET /api/materias ────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $materias = $request->user()
            ->materias()
            ->orderBy('nombre')
            ->get();

        return response()->json($materias);
    }

    // ─── POST /api/materias ───────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
        ], [
            'nombre.required' => 'El nombre de la materia es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 255 caracteres.',
        ]);

        $materia = $request->user()->materias()->create([
            'nombre' => $data['nombre'],
        ]);

        return response()->json($materia, 201);
    }

    // ─── DELETE /api/materias/:id ─────────────────────────────────────────────
    public function destroy(Request $request, int $id): JsonResponse
    {
        $materia = $request->user()
            ->materias()
            ->findOrFail($id);

        $materia->delete();

        return response()->json(null, 204);
    }
}
