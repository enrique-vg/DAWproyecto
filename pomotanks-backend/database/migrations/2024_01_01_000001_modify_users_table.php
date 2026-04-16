<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Modifica la tabla users que ya crea Laravel por defecto.
        // Si tu tabla users aún no existe, crea una nueva con Schema::create.
        // Si ya existe (instalación fresca), modifica con Schema::table.
        Schema::table('users', function (Blueprint $table) {
            // Renombramos 'name' a 'nombre' si existe, o añadimos 'nombre'
            if (Schema::hasColumn('users', 'name')) {
                $table->renameColumn('name', 'nombre');
            } else {
                $table->string('nombre')->after('id');
            }

            // Campo esPremium
            if (!Schema::hasColumn('users', 'es_premium')) {
                $table->boolean('es_premium')->default(false)->after('password');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'nombre')) {
                $table->renameColumn('nombre', 'name');
            }
            if (Schema::hasColumn('users', 'es_premium')) {
                $table->dropColumn('es_premium');
            }
        });
    }
};
