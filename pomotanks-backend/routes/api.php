<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\HitoController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\ProgresoController;
use App\Http\Controllers\SesionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — PomoTanks
|--------------------------------------------------------------------------
*/

// ─── Rutas públicas (sin autenticación) ──────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ─── Rutas protegidas (requieren sesión Sanctum) ──────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout',   [AuthController::class, 'logout']);
    Route::get('/user',      [AuthController::class, 'user']);
    Route::patch('/user',    [AuthController::class, 'updateUser']);

    // Configuración
    Route::get('/configuracion', [ConfiguracionController::class, 'show']);
    Route::put('/configuracion', [ConfiguracionController::class, 'update']);

    // Materias
    Route::get('/materias',        [MateriaController::class, 'index']);
    Route::post('/materias',       [MateriaController::class, 'store']);
    Route::delete('/materias/{id}', [MateriaController::class, 'destroy']);

    // Sesiones y periodos
    Route::post('/sesiones',                      [SesionController::class, 'store']);
    Route::patch('/sesiones/{id}/finalizar',      [SesionController::class, 'finalizar']);
    Route::post('/sesiones/{id}/periodos',        [SesionController::class, 'storePeriodo']);

    // Estadísticas
    Route::get('/progreso', [ProgresoController::class, 'show']);
    Route::get('/hitos',    [HitoController::class, 'index']);
});
