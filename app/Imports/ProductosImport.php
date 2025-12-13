<?php

namespace App\Imports;

use App\Models\Producto;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductosImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Producto([
            'unidad_codigo'           => $row['unidad_codigo'],
            'afectacion_tipo_codigo'  => $row['afectacion_tipo_codigo'],
            'codigo'                  => $row['codigo'],
            'nombre'                  => $row['nombre'],
            'descripcion'             => $row['descripcion'],
            'imagen'                  => $row['imagen'],
            'precio_unitario'         => $row['precio_unitario'],
            'stock'                   => $row['stock'],
        ]);
    }
}
