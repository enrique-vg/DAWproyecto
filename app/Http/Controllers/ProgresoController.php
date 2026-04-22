<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class ProgresoController extends Controller
{
    // ─── GET /api/progreso?periodo=semana&fecha=2024-01-15 ───────────────────
    public function show(Request $request): JsonResponse
    {
        $periodo = $request->query('periodo', 'semana');
        $fecha   = $request->query('fecha')
            ? Carbon::parse($request->query('fecha'))
            : Carbon::now();
        \Illuminate\Support\Facades\Log::info('Progreso request', [
            'periodo' => $periodo,
            'fecha'   => $fecha->toDateTimeString(),
            'tz'      => $fecha->timezoneName,
        ]);
        $user = $request->user();

        // Total global del usuario
        $totalSesiones = $user->sesiones()->where('completado', true)->count();

        // $totalTiempo = $user->sesiones()
        //     ->where('completado', true)
        //     ->join('periodos', 'sesiones.id', '=', 'periodos.sesion_id')
        //     ->where('periodos.tipo', 'TRABAJO')
        //     ->where('periodos.completado', true)
        //     ->sum('periodos.duracion');
        $totalTiempo = \App\Models\Sesion::query()
            ->where('sesiones.usuario_id', $user->id)
            ->where('sesiones.completado', true)
            ->join('periodos', 'sesiones.id', '=', 'periodos.sesion_id')
            ->where('periodos.tipo', 'TRABAJO')
            ->where('periodos.completado', true)
            ->sum('periodos.duracion');
        // Datos del gráfico según periodo
        [$labels, $datos] = $this->calcularGrafico($user, $periodo, $fecha);

        // return response()->json([
        //     'totalSesiones' => $totalSesiones,
        //     'totalTiempo'   => (int) $totalTiempo,
        //     'grafico'       => [
        //         'labels' => $labels,
        //         'datos'  => $datos,
        //     ],
        // ]);
        return response()->json([
            'totalSesiones' => $totalSesiones,
            'totalTiempo'   => (float) $totalTiempo,
            'grafico'       => [
                'labels' => $labels,
                'datos'  => $datos,
            ],
        ]);
    }

    private function calcularGrafico(\App\Models\User $user, string $periodo, Carbon $fecha): array
    {
        switch ($periodo) {
            case 'dia':
                return $this->graficoDia($user, $fecha);
            case 'mes':
                return $this->graficoMes($user, $fecha);
            case 'año':
                return $this->graficoAnio($user, $fecha);
            case 'semana':
            default:
                return $this->graficoSemana($user, $fecha);
        }
    }

    // Semana: lunes a domingo de la semana que contiene $fecha
    // private function graficoSemana(\App\Models\User $user, Carbon $fecha): array
    // {
    //     $labels = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
    //     $inicio = $fecha->copy()->startOfWeek(Carbon::MONDAY);

    //     $datos = [];
    //     for ($i = 0; $i < 7; $i++) {
    //         $dia    = $inicio->copy()->addDays($i);
    //         $datos[] = $this->minutosTrabajoEnDia($user, $dia);
    //     }

    //     return [$labels, $datos];
    // }
    private function graficoSemana(\App\Models\User $user, Carbon $fecha): array
    {
        $labels = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        $inicio = $fecha->copy()->startOfWeek(Carbon::MONDAY);

        $datos = [];
        for ($i = 0; $i < 7; $i++) {
            $dia     = $inicio->copy()->addDays($i);
            $minutos = $this->minutosTrabajoEnDia($user, $dia);
            \Illuminate\Support\Facades\Log::info("Dia $i", [
                'dia'     => $dia->toDateString(),
                'minutos' => $minutos,
            ]);
            $datos[] = $minutos;
        }

        return [$labels, $datos];
    }
    // Día: franjas de 2 horas (00h, 02h, ... 22h)
    private function graficoDia(\App\Models\User $user, Carbon $fecha): array
    {
        $labels = [];
        $datos  = [];

        for ($h = 0; $h < 24; $h += 2) {
            $labels[] = str_pad($h, 2, '0', STR_PAD_LEFT) . 'h';
            $desde    = $fecha->copy()->startOfDay()->addHours($h);
            $hasta    = $desde->copy()->addHours(2);

            $minutos = $this->minutosTrabajoEnRango($user, $desde, $hasta);
            $datos[] = $minutos;
        }

        return [$labels, $datos];
    }

    // Mes: día 1 a último día del mes
    private function graficoMes(\App\Models\User $user, Carbon $fecha): array
    {
        $diasEnMes = $fecha->daysInMonth;
        $labels    = [];
        $datos     = [];

        for ($d = 1; $d <= $diasEnMes; $d++) {
            $labels[] = (string) $d;
            $dia      = $fecha->copy()->setDay($d)->startOfDay();
            $datos[]  = $this->minutosTrabajoEnDia($user, $dia);
        }

        return [$labels, $datos];
    }

    // Año: enero a diciembre
    private function graficoAnio(\App\Models\User $user, Carbon $fecha): array
    {
        $labels = [
            'Ene',
            'Feb',
            'Mar',
            'Abr',
            'May',
            'Jun',
            'Jul',
            'Ago',
            'Sep',
            'Oct',
            'Nov',
            'Dic'
        ];
        $datos  = [];

        for ($m = 1; $m <= 12; $m++) {
            $desde   = Carbon::create($fecha->year, $m, 1)->startOfMonth();
            $hasta   = $desde->copy()->endOfMonth();
            $datos[] = $this->minutosTrabajoEnRango($user, $desde, $hasta);
        }

        return [$labels, $datos];
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────
    private function minutosTrabajoEnDia(\App\Models\User $user, Carbon $dia): int
    {
        return $this->minutosTrabajoEnRango(
            $user,
            $dia->copy()->startOfDay(),
            $dia->copy()->endOfDay()
        );
    }

    // private function minutosTrabajoEnRango(\App\Models\User $user, Carbon $desde, Carbon $hasta): int
    // {
    //     $minutos = $user->sesiones()
    //         ->whereBetween('fechaInicio', [$desde, $hasta])
    //         ->join('periodos', 'sesiones.id', '=', 'periodos.sesion_id')
    //         ->where('periodos.tipo', 'TRABAJO')
    //         ->where('periodos.completado', true)
    //         ->sum('periodos.duracion');

    //     return (int) $minutos;
    // }
    // private function minutosTrabajoEnRango(\App\Models\User $user, Carbon $desde, Carbon $hasta): int
    // {
    //     $minutos = \App\Models\Sesion::query()
    //         ->where('sesiones.usuario_id', $user->id)
    //         ->whereBetween('sesiones.fechaInicio', [$desde, $hasta])
    //         ->where('sesiones.completado', true)
    //         ->join('periodos', 'sesiones.id', '=', 'periodos.sesion_id')
    //         ->where('periodos.tipo', 'TRABAJO')
    //         ->where('periodos.completado', true)
    //         ->sum('periodos.duracion');

    //     return (int) $minutos;
    // }
    private function minutosTrabajoEnRango(\App\Models\User $user, Carbon $desde, Carbon $hasta): int
    {
        $minutos = \App\Models\Sesion::query()
            ->where('sesiones.usuario_id', $user->id)
            ->whereBetween('sesiones.fechaInicio', [
                $desde->format('Y-m-d H:i:s'),
                $hasta->format('Y-m-d H:i:s'),
            ])
            ->where('sesiones.completado', true)
            ->join('periodos', 'sesiones.id', '=', 'periodos.sesion_id')
            ->where('periodos.tipo', 'TRABAJO')
            ->where('periodos.completado', true)
            ->sum('periodos.duracion');

        // return (int) $minutos;
        return (float) $minutos;
    }
}
