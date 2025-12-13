<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->char('unidad_codigo', 3);
            $table->char('afectacion_tipo_codigo', 2);
            $table->string('codigo', 50)->nullable(); // Código interno opcional
            $table->string('nombre', 50);
            $table->string('descripcion', 255)->nullable();
            $table->string('imagen', 50)->nullable();
            $table->decimal('precio_unitario', 6, 2);
            $table->timestamps();

            $table->foreign('unidad_codigo')->references('codigo')->on('unidades');
            $table->foreign('afectacion_tipo_codigo')->references('codigo')->on('afectacion_tipos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
