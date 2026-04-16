<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HitoController extends Controller
{
    // ─── GET /api/hitos ───────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $hitos = $request->user()
            ->hitos()
            ->orderByDesc('fecha')
            ->get();

        return response()->json($hitos);
    }
}
