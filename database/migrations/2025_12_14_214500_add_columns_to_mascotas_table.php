<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mascotas', function (Blueprint $table) {
            if (!Schema::hasColumn('mascotas', 'user_id')) {
                $table->foreignId('user_id')->constrained()->cascadeOnDelete()->after('id');
            }
            if (!Schema::hasColumn('mascotas', 'nombre')) {
                $table->string('nombre')->after('user_id');
            }
            if (!Schema::hasColumn('mascotas', 'especie')) {
                $table->string('especie')->after('nombre');
            }
            if (!Schema::hasColumn('mascotas', 'raza')) {
                $table->string('raza')->nullable()->after('especie');
            }
            if (!Schema::hasColumn('mascotas', 'sexo')) {
                $table->enum('sexo', ['m','h'])->nullable()->after('raza');
            }
            if (!Schema::hasColumn('mascotas', 'fecha_nacimiento')) {
                $table->date('fecha_nacimiento')->nullable()->after('sexo');
            }
            if (!Schema::hasColumn('mascotas', 'notas')) {
                $table->text('notas')->nullable()->after('fecha_nacimiento');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mascotas', function (Blueprint $table) {
            if (Schema::hasColumn('mascotas', 'notas')) {
                $table->dropColumn('notas');
            }
            if (Schema::hasColumn('mascotas', 'fecha_nacimiento')) {
                $table->dropColumn('fecha_nacimiento');
            }
            if (Schema::hasColumn('mascotas', 'sexo')) {
                $table->dropColumn('sexo');
            }
            if (Schema::hasColumn('mascotas', 'raza')) {
                $table->dropColumn('raza');
            }
            if (Schema::hasColumn('mascotas', 'especie')) {
                $table->dropColumn('especie');
            }
            if (Schema::hasColumn('mascotas', 'nombre')) {
                $table->dropColumn('nombre');
            }
            if (Schema::hasColumn('mascotas', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
