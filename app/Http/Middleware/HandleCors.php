<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleCors
{
    /**
     * Gestiona las cabeceras CORS y responde a las peticiones preflight OPTIONS
     * que el navegador lanza antes de cada request con credenciales.
     *
     * Necesario para que Vue (localhost:5173) pueda hablar con Laravel (localhost:8000).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Respuesta inmediata a preflight OPTIONS
        if ($request->isMethod('OPTIONS')) {
            return response()->noContent()
                ->header('Access-Control-Allow-Origin',      'http://localhost:5173')
                ->header('Access-Control-Allow-Methods',     'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers',     'Content-Type, X-Requested-With, X-XSRF-TOKEN, Accept')
                ->header('Access-Control-Allow-Credentials', 'true');
        }

        $response = $next($request);

        $response->headers->set('Access-Control-Allow-Origin',      'http://localhost:5173');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}
