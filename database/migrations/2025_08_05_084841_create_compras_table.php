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
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->char('comprobante_tipo_codigo', 2);
            $table->unsignedBigInteger('proveedor_id');
            $table->string('serie', 4);
            $table->integer('correlativo');
            $table->string('forma_pago', 7);
            $table->date('fecha');            
            $table->decimal('op_gravada', 8, 2);
            $table->decimal('op_exonerada', 8, 2);
            $table->decimal('op_inafecta', 8, 2);
            $table->decimal('impuesto', 8, 2);
            $table->decimal('total', 8, 2);
            $table->string('estado', 20);
            $table->timestamps();

            // Claves foráneas (si existen las tablas relacionadas)
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('proveedor_id')->references('id')->on('proveedores');
            $table->foreign('comprobante_tipo_codigo')->references('codigo')->on('comprobante_tipos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
