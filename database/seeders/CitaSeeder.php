<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cita;
use Carbon\Carbon;

class CitaSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        Cita::insert([
            [
                'fecha_hora' => $now->copy()->addDays(1)->format('Y-m-d H:i'),
                'cliente_nombre' => 'Juan Perez',
                'cliente_telefono' => '987654321',
                'mascota_nombre' => 'Firulais',
                'mascota_especie' => 'Perro',
                'motivo' => 'Vacunación anual',
                'estado' => 'pendiente',
                'precio' => 50.00,
                'notas' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'fecha_hora' => $now->copy()->addDays(2)->format('Y-m-d H:i'),
                'cliente_nombre' => 'María López',
                'cliente_telefono' => '912345678',
                'mascota_nombre' => 'Michi',
                'mascota_especie' => 'Gato',
                'motivo' => 'Consulta por pérdida de apetito',
                'estado' => 'confirmada',
                'precio' => null,
                'notas' => 'Traer registro de vacunas',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
