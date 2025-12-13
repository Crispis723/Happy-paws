<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $table = 'compras';

    protected $fillable = [
        'user_id',
        'comprobante_tipo_codigo',
        'proveedor_id',
        'serie',
        'correlativo',
        'forma_pago',
        'fecha',
        'op_gravada',
        'op_exonerada',
        'op_inafecta',
        'impuesto',
        'total',
        'estado'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function comprobanteTipo()
    {
        return $this->belongsTo(ComprobanteTipo::class, 'comprobante_tipo_codigo', 'codigo');
    }

    public function detalles()
    {
        return $this->hasMany(CompraDetalle::class, 'compra_id');
    }
}
