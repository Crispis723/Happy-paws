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
        Schema::create('documento_tipos', function (Blueprint $table) {
            $table->char('codigo', 2)->primary();
            $table->string('descripcion', 50);
        });

        DB::table('documento_tipos')->insert([
            ['codigo' => '00', 'descripcion' => 'Doc. Tributario no domiciliado'],
            ['codigo' => '01', 'descripcion' => 'DNI'],
            ['codigo' => '04', 'descripcion' => 'Carnet de extranjería'],
            ['codigo' => '06', 'descripcion' => 'RUC'],
            ['codigo' => '07', 'descripcion' => 'Pasaporte']
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documento_tipos');
    }
};
