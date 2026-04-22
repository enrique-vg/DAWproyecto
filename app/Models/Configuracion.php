<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuraciones';

    protected $fillable = [
        'tiempoTrabajo',
        'tiempoDescanso',
        'tiempoDescansoLargo',
        'usuario_id',
    ];

    protected $casts = [
        // 'tiempoTrabajo'       => 'integer',
        // 'tiempoDescanso'      => 'integer',
        // 'tiempoDescansoLargo' => 'integer',
        'tiempoTrabajo'       => 'float',
        'tiempoDescanso'      => 'float',
        'tiempoDescansoLargo' => 'float',
    ];

    // Ocultamos campos de auditoría del JSON que ve el frontend
    protected $hidden = ['id', 'usuario_id', 'created_at', 'updated_at'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
