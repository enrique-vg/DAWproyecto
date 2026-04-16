<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sesion extends Model
{
    protected $table = 'sesiones';

    protected $fillable = [
        'fechaInicio',
        'fechaFin',
        'completado',
        'usuario_id',
        'materia_id',
    ];

    protected $casts = [
        'fechaInicio' => 'datetime',
        'fechaFin'    => 'datetime',
        'completado'  => 'boolean',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    public function periodos()
    {
        return $this->hasMany(Periodo::class, 'sesion_id');
    }
}
