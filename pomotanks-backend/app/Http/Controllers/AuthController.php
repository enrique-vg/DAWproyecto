<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ─── POST /api/register ──────────────────────────────────────────────────
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'                => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'unique:users,email'],
            'password'              => ['required', 'confirmed', Password::min(8)],
        ], [
            'nombre.required'       => 'El nombre es obligatorio.',
            'email.required'        => 'El correo es obligatorio.',
            'email.email'           => 'El correo no tiene un formato válido.',
            'email.unique'          => 'El correo ya está en uso.',
            'password.required'     => 'La contraseña es obligatoria.',
            'password.confirmed'    => 'Las contraseñas no coinciden.',
            'password.min'          => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        $user = User::create([
            'nombre'     => $data['nombre'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
            'es_premium' => false,
        ]);

        // Crear configuración por defecto
        Configuracion::create([
            'usuario_id'          => $user->id,
            'tiempoTrabajo'       => 25,
            'tiempoDescanso'      => 5,
            'tiempoDescansoLargo' => 15,
        ]);

        Auth::login($user);

        return response()->json(['user' => $user], 201);
    }

    // ─── POST /api/login ─────────────────────────────────────────────────────
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'El correo es obligatorio.',
            'email.email'       => 'El correo no tiene un formato válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no son correctas.'],
            ]);
        }

        $request->session()->regenerate();

        return response()->json(['user' => Auth::user()]);
    }

    // ─── POST /api/logout ────────────────────────────────────────────────────
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(null, 204);
    }

    // ─── GET /api/user ───────────────────────────────────────────────────────
    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    // ─── PATCH /api/user ─────────────────────────────────────────────────────
    public function updateUser(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:255'],
        ], [
            'nombre.string' => 'El nombre debe ser texto.',
        ]);

        $user->update($data);

        return response()->json(['user' => $user->fresh()]);
    }
}
