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
        Schema::create('comprobante_tipos', function (Blueprint $table) {
            $table->char('codigo', 2)->primary();
            $table->string('descripcion', 50);
            $table->timestamps();
        });
        DB::table('comprobante_tipos')->insert([
            ['codigo' => '00', 'descripcion' => 'Otros'],
            ['codigo' => '01', 'descripcion' => 'Factura'],
            ['codigo' => '03', 'descripcion' => 'Boleta de Venta'],
            ['codigo' => '07', 'descripcion' => 'Nota de Crédito'],
            ['codigo' => '08', 'descripcion' => 'Nota de Débito'],
            ['codigo' => '09', 'descripcion' => 'Guía de Remisión - Remitente']
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comprobante_tipos');
    }
};
