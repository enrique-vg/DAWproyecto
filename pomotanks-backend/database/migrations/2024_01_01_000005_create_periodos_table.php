<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodos', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['TRABAJO', 'DESCANSO_CORTO', 'DESCANSO_LARGO']);
            $table->unsignedInteger('duracion'); // minutos
            $table->boolean('completado')->default(false);
            $table->foreignId('sesion_id')->constrained('sesiones')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos');
    }
};
