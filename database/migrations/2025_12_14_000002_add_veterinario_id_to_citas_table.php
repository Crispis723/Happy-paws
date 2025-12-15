<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('citas') && !Schema::hasColumn('citas', 'veterinario_id')) {
            Schema::table('citas', function (Blueprint $table) {
                $table->unsignedBigInteger('veterinario_id')->nullable()->after('mascota_id');
                $table->foreign('veterinario_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            if (Schema::hasColumn('citas', 'veterinario_id')) {
                $table->dropForeign(['veterinario_id']);
                $table->dropColumn('veterinario_id');
            }
        });
    }
};
