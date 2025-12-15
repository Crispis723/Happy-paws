<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha_hora');           // Fecha y hora de la cita
            $table->string('cliente_nombre');         // Nombre del dueño
            $table->string('cliente_telefono');       // Teléfono del dueño
            $table->string('mascota_nombre');         // Nombre de la mascota
            $table->string('mascota_especie');        // Especie (perro, gato, etc)
            $table->text('motivo');                   // Motivo de la consulta
            $table->enum('estado', ['pendiente', 'confirmada', 'completada', 'cancelada'])->default('pendiente');
            $table->decimal('precio', 8, 2)->nullable();
            $table->text('notas')->nullable();        // Notas del veterinario
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};