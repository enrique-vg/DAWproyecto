<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use App\Models\Hito;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\Sesion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Usuario de prueba ────────────────────────────────────────────────
        $user = User::firstOrCreate(
            ['email' => 'test@pomotanks.dev'],
            [
                'nombre'     => 'Usuario Test',
                'password'   => Hash::make('password'),
                'es_premium' => false,
            ]
        );

        // ─── Configuración ────────────────────────────────────────────────────
        Configuracion::firstOrCreate(
            ['usuario_id' => $user->id],
            [
                'tiempoTrabajo'       => 25,
                'tiempoDescanso'      => 5,
                'tiempoDescansoLargo' => 15,
            ]
        );

        // ─── Materias ─────────────────────────────────────────────────────────
        $materiasNombres = ['Matemáticas', 'Historia', 'Programación', 'Inglés'];
        $materias = [];
        foreach ($materiasNombres as $nombre) {
            $materias[] = Materia::firstOrCreate(
                ['nombre' => $nombre, 'usuario_id' => $user->id]
            );
        }

        // ─── Sesiones de los últimos 7 días ───────────────────────────────────
        $ahora = Carbon::now();
        for ($d = 6; $d >= 0; $d--) {
            $dia = $ahora->copy()->subDays($d)->setTime(9, 0);

            // 2 o 3 sesiones por día
            $numSesiones = rand(2, 3);
            for ($s = 0; $s < $numSesiones; $s++) {
                $inicio = $dia->copy()->addMinutes($s * 60);
                $fin    = $inicio->copy()->addMinutes(35);

                $sesion = Sesion::create([
                    'fechaInicio' => $inicio,
                    'fechaFin'    => $fin,
                    'completado'  => true,
                    'usuario_id'  => $user->id,
                    'materia_id'  => $materias[array_rand($materias)]->id,
                ]);

                // Periodos: 1 trabajo + 1 descanso
                Periodo::create([
                    'tipo'       => 'TRABAJO',
                    'duracion'   => 25,
                    'completado' => true,
                    'sesion_id'  => $sesion->id,
                ]);
                Periodo::create([
                    'tipo'       => 'DESCANSO_CORTO',
                    'duracion'   => 5,
                    'completado' => true,
                    'sesion_id'  => $sesion->id,
                ]);
            }
        }

        // ─── Hito de bienvenida ───────────────────────────────────────────────
        Hito::firstOrCreate(
            ['descripcion' => 'Primera sesión completada 🎉', 'usuario_id' => $user->id],
            ['fecha' => Carbon::now()->subDays(6)]
        );

        $this->command->info('✅ Seeder completado — test@pomotanks.dev / password');
    }
}
