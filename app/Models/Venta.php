<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'user_id',
        'comprobante_tipo_codigo',
        'cliente_id',
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

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function comprobanteTipo()
    {
        return $this->belongsTo(ComprobanteTipo::class, 'comprobante_tipo_codigo', 'codigo');
    }

    public function detalles()
    {
        return $this->hasMany(VentaDetalle::class, 'venta_id');
    }
}
