<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ConfiguracionController extends Controller
{
    // ─── GET /api/configuracion ───────────────────────────────────────────────
    public function show(Request $request): JsonResponse
    {
        $config = $request->user()->configuracion;

        return response()->json($config);
    }

    // ─── PUT /api/configuracion ───────────────────────────────────────────────
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            // 'tiempoTrabajo'       => ['sometimes', 'integer', 'min:1', 'max:120'],
            // 'tiempoDescanso'      => ['sometimes', 'integer', 'min:1', 'max:60'],
            // 'tiempoDescansoLargo' => ['sometimes', 'integer', 'min:1', 'max:60'],
            'tiempoTrabajo'       => ['sometimes', 'numeric', 'min:0.1', 'max:120'],
            'tiempoDescanso'      => ['sometimes', 'numeric', 'min:0.1', 'max:60'],
            'tiempoDescansoLargo' => ['sometimes', 'numeric', 'min:0.1', 'max:60'],
        ], [
            // 'tiempoTrabajo.integer'       => 'El tiempo de trabajo debe ser un número entero.',
            // 'tiempoTrabajo.min'           => 'El tiempo de trabajo mínimo es 1 minuto.',
            // 'tiempoTrabajo.max'           => 'El tiempo de trabajo máximo es 120 minutos.',
            // 'tiempoDescanso.integer'      => 'El tiempo de descanso debe ser un número entero.',
            // 'tiempoDescansoLargo.integer' => 'El tiempo de descanso largo debe ser un número entero.',
            'tiempoTrabajo.numeric'       => 'El tiempo de trabajo debe ser un número.',
            'tiempoTrabajo.min'           => 'El tiempo de trabajo mínimo es 0.1 minutos.',
            'tiempoTrabajo.max'           => 'El tiempo de trabajo máximo es 120 minutos.',
            'tiempoDescanso.numeric'      => 'El tiempo de descanso debe ser un número.',
            'tiempoDescanso.min'          => 'El tiempo de descanso mínimo es 0.1 minutos.',
            'tiempoDescanso.max'          => 'El tiempo de descanso máximo es 60 minutos.',
            'tiempoDescansoLargo.numeric' => 'El tiempo de descanso largo debe ser un número.',
            'tiempoDescansoLargo.min'     => 'El tiempo de descanso largo mínimo es 0.1 minutos.',
            'tiempoDescansoLargo.max'     => 'El tiempo de descanso largo máximo es 60 minutos.',
        ]);

        $config = $request->user()->configuracion;
        $config->update($data);

        return response()->json($config->fresh());
    }
}
