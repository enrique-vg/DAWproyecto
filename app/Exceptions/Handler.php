<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    // ─── Formato JSON uniforme para todos los errores ─────────────────────────
    public function render($request, Throwable $e)
    {
        // Forzar siempre respuesta JSON si viene de la API
        if ($request->is('api/*') || $request->expectsJson()) {

            // 422 — Errores de validación
            if ($e instanceof ValidationException) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors'  => $e->errors(),
                ], 422);
            }

            // 401 — No autenticado
            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'message' => 'No autenticado.',
                ], 401);
            }

            // 404 — Ruta o recurso no encontrado
            if ($e instanceof NotFoundHttpException) {
                return response()->json([
                    'message' => 'Recurso no encontrado.',
                ], 404);
            }

            // 500 — Cualquier otro error
            return response()->json([
                'message' => config('app.debug')
                    ? $e->getMessage()
                    : 'Ha ocurrido un error.',
            ], 500);
        }

        return parent::render($request, $e);
    }
}
