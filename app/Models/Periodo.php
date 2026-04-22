<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{
    protected $table = 'periodos';

    protected $fillable = [
        'tipo',
        'duracion',
        'completado',
        'sesion_id',
    ];

    protected $casts = [
        'completado' => 'boolean',
        // 'duracion'   => 'integer',
        'duracion' => 'float',
    ];

    public function sesion()
    {
        return $this->belongsTo(Sesion::class, 'sesion_id');
    }
}
