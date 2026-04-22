<?php

namespace App\Http\Controllers;

use App\Models\Hito;
use App\Models\Periodo;
use App\Models\Sesion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class SesionController extends Controller
{
    // ─── POST /api/sesiones ───────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'materia_id' => ['nullable', 'integer', 'exists:materias,id'],
        ]);

        // Si hay materia_id, verificar que pertenezca al usuario
        if (!empty($data['materia_id'])) {
            $request->user()
                ->materias()
                ->findOrFail($data['materia_id']);
        }

        $sesion = $request->user()->sesiones()->create([
            'fechaInicio' => Carbon::now(),
            'completado'  => false,
            'materia_id'  => $data['materia_id'] ?? null,
        ]);

        return response()->json($sesion, 201);
    }

    // ─── PATCH /api/sesiones/:id/finalizar ───────────────────────────────────
    public function finalizar(Request $request, int $id): JsonResponse
    {
        $sesion = $request->user()
            ->sesiones()
            ->findOrFail($id);

        $data = $request->validate([
            'completado' => ['required', 'boolean'],
        ], [
            'completado.required' => 'El campo completado es obligatorio.',
            'completado.boolean'  => 'El campo completado debe ser true o false.',
        ]);

        $sesion->update([
            'fechaFin'   => Carbon::now(),
            'completado' => $data['completado'],
        ]);

        // Si se completó la sesión, comprobar hitos
        if ($data['completado']) {
            $this->comprobarHitos($request->user());
        }

        return response()->json($sesion->fresh());
    }

    // ─── POST /api/sesiones/:id/periodos ─────────────────────────────────────
    public function storePeriodo(Request $request, int $id): JsonResponse
    {
        // Verificar que la sesión pertenece al usuario
        $sesion = $request->user()
            ->sesiones()
            ->findOrFail($id);

        $data = $request->validate([
            'tipo'       => ['required', 'in:TRABAJO,DESCANSO_CORTO,DESCANSO_LARGO'],
            // 'duracion'   => ['required', 'integer', 'min:1'],
            'duracion' => ['required', 'numeric', 'min:0.1'],
            'completado' => ['required', 'boolean'],
        ], [
            'tipo.required'       => 'El tipo de periodo es obligatorio.',
            'tipo.in'             => 'El tipo debe ser TRABAJO, DESCANSO_CORTO o DESCANSO_LARGO.',
            'duracion.required'   => 'La duración es obligatoria.',
            // 'duracion.integer'    => 'La duración debe ser un número entero.',
            'duracion.numeric' => 'La duración debe ser un número.',
            'completado.required' => 'El campo completado es obligatorio.',
        ]);

        $periodo = $sesion->periodos()->create($data);

        return response()->json($periodo, 201);
    }

    // ─── Lógica de hitos automáticos ─────────────────────────────────────────
    private function comprobarHitos(\App\Models\User $user): void
    {
        $totalSesiones = $user->sesiones()
            ->where('completado', true)
            ->count();

        $hitosExistentes = $user->hitos()
            ->pluck('descripcion')
            ->toArray();

        $posiblesHitos = [
            1   => 'Primera sesión completada 🎉',
            10  => '10 pomodoros completados 🔟',
            25  => '25 pomodoros completados 🌟',
            50  => '50 pomodoros completados 🏆',
            100 => '100 pomodoros completados 🚀',
        ];

        foreach ($posiblesHitos as $umbral => $descripcion) {
            if ($totalSesiones >= $umbral && !in_array($descripcion, $hitosExistentes)) {
                Hito::create([
                    'descripcion' => $descripcion,
                    'fecha'       => now(),
                    'usuario_id'  => $user->id,
                ]);
            }
        }

        // Hito: 5 pomodoros en un día
        $hoyCompletas = $user->sesiones()
            ->where('completado', true)
            ->whereDate('fechaInicio', today())
            ->count();

        $desc5hoy = '5 pomodoros en un mismo día ⚡';
        if ($hoyCompletas >= 5 && !in_array($desc5hoy, $hitosExistentes)) {
            Hito::create([
                'descripcion' => $desc5hoy,
                'fecha'       => now(),
                'usuario_id'  => $user->id,
            ]);
        }
    }
}
