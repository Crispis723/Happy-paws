<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            if (!Schema::hasColumn('citas', 'mascota_id')) {
                $table->foreignId('mascota_id')->nullable()->constrained('mascotas')->nullOnDelete()->after('cliente_telefono');
            }
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            if (Schema::hasColumn('citas', 'mascota_id')) {
                $table->dropForeign(['mascota_id']);
                $table->dropColumn('mascota_id');
            }
        });
    }
};
