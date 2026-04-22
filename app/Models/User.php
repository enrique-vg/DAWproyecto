<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'es_premium',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'es_premium'        => 'boolean',
    ];

    // ─── Accessor: expone es_premium como esPremium en JSON ───────────────────
    // protected function esPremium(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn() => $this->es_premium,
    //     );
    // }

    // Sobreescribimos toArray para que el JSON use esPremium (camelCase)
    // public function toArray(): array
    // {
    //     $array = parent::toArray();

    //     // Añadimos la versión camelCase y eliminamos la snake_case
    //     $array['esPremium'] = $this->es_premium;
    //     unset($array['es_premium']);

    //     return $array;
    // }
    public function toArray(): array
    {
        $array = parent::toArray();

        // Renombrar es_premium → esPremium para el frontend
        if (array_key_exists('es_premium', $array)) {
            $array['esPremium'] = (bool) $array['es_premium'];
            unset($array['es_premium']);
        }

        return $array;
    }
    // ─── Relaciones ───────────────────────────────────────────────────────────
    public function configuracion()
    {
        return $this->hasOne(Configuracion::class, 'usuario_id');
    }

    public function sesiones()
    {
        return $this->hasMany(Sesion::class, 'usuario_id');
    }

    public function progreso()
    {
        return $this->hasOne(Progreso::class, 'usuario_id');
    }

    public function hitos()
    {
        return $this->hasMany(Hito::class, 'usuario_id');
    }

    public function materias()
    {
        return $this->hasMany(Materia::class, 'usuario_id');
    }
}
