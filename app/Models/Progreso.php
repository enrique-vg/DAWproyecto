<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Progreso extends Model
{
    protected $table = 'progresos';

    protected $fillable = [
        'totalSesiones',
        'totalTiempo',
        'usuario_id',
    ];

    protected $casts = [
        'totalSesiones' => 'integer',
        'totalTiempo'   => 'integer',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
