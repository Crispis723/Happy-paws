<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Agrega campos para distinguir tipos de usuarios y subroles de empleados.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'user_type')) {
                $table->enum('user_type', ['admin', 'staff', 'public'])
                    ->default('public')
                    ->after('email')
                    ->comment('Tipo principal de usuario');
                $table->index('user_type');
            }
            if (!Schema::hasColumn('users', 'staff_type')) {
                $table->enum('staff_type', ['contador', 'veterinario', 'recepcionista', 'gerente'])
                    ->nullable()
                    ->after('user_type')
                    ->comment('Categoría del empleado (solo para staff)');
                $table->index('staff_type');
            }
            if (!Schema::hasColumn('users', 'activo')) {
                $table->boolean('activo')
                    ->default(true)
                    ->after('staff_type')
                    ->comment('Si el usuario está activo');
                $table->index('activo');
            }
            if (!Schema::hasColumn('users', 'telefono')) {
                $table->string('telefono')
                    ->nullable()
                    ->after('activo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'user_type')) {
                $table->dropIndex(['user_type']);
                $table->dropColumn('user_type');
            }
            if (Schema::hasColumn('users', 'staff_type')) {
                $table->dropIndex(['staff_type']);
                $table->dropColumn('staff_type');
            }
            if (Schema::hasColumn('users', 'activo')) {
                $table->dropIndex(['activo']);
                $table->dropColumn('activo');
            }
            if (Schema::hasColumn('users', 'telefono')) {
                $table->dropColumn('telefono');
            }
        });
    }
};
