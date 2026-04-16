<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hito extends Model
{
    protected $table = 'hitos';

    protected $fillable = [
        'descripcion',
        'fecha',
        'usuario_id',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
