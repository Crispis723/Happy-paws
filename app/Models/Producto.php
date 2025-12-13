<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Producto extends Model
{
    protected $fillable = [
        'unidad_codigo',
        'afectacion_tipo_codigo',
        'codigo',
        'nombre',
        'descripcion',
        'imagen',
        'precio_unitario',
        'stock'
    ];

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_codigo', 'codigo');
    }

    public function afectacionTipo()
    {
        return $this->belongsTo(AfectacionTipo::class, 'afectacion_tipo_codigo', 'codigo');
    }

    public static function updateStock(bool $increase, array $detalles) {
        DB::transaction(function () use ($increase, $detalles) {
            foreach ($detalles as $detalle) {
                $producto = self::findOrFail($detalle['producto_id']);
                $cantidad = floatval($detalle['cantidad']);
                if ($increase) {
                    $producto->stock += $cantidad;
                } else {
                    if ($producto->stock < $cantidad) {
                        throw new \Exception("El producto {$producto->nombre} no tiene suficiente stock.");
                    }
                    $producto->stock -= $cantidad;
                }
                $producto->save();
            }
        });
    }

}
