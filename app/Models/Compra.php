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

    // Asegura que 'fecha' se maneje como instancia de Carbon
    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function proveedor()   // al tener un solo proveedor
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function comprobanteTipo()
    {
        return $this->belongsTo(ComprobanteTipo::class, 'comprobante_tipo_codigo', 'codigo');
    }

    public function detalles() //se realiza por que tiene muchos detalles
    {
        return $this->hasMany(CompraDetalle::class, 'compra_id');
    }
}
