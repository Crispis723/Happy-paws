<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Unidad;

class UnidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unidades = [
            ['codigo' => 'NIU', 'descripcion' => 'UNIDAD (BIENES)'],
            ['codigo' => 'BO',  'descripcion' => 'BOTELLAS'],
            ['codigo' => 'BX',  'descripcion' => 'CAJA'],
            ['codigo' => 'CMT', 'descripcion' => 'CENTÍMETRO LINEAL'],
            ['codigo' => 'CEN', 'descripcion' => 'CIENTO DE UNIDADES'],
            ['codigo' => 'DZN', 'descripcion' => 'DOCENA'],
            ['codigo' => 'KGM', 'descripcion' => 'KILOGRAMO'],
            ['codigo' => 'LBR', 'descripcion' => 'LIBRAS'],
            ['codigo' => 'LTR', 'descripcion' => 'LITRO'],
            ['codigo' => 'FTK', 'descripcion' => 'PIES CUADRADOS'],            
            ['codigo' => 'ZZ',  'descripcion' => 'UNIDAD (SERVICIOS)'],
            ['codigo' => 'GLL', 'descripcion' => 'US GALÓN (3,7843 L)']
        ];
        foreach ($unidades as $unidad) {
            Unidad::updateOrCreate(
                ['codigo' => $unidad['codigo']],
                ['descripcion' => $unidad['descripcion']]
            );
        }
    }
}
