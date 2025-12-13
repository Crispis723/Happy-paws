<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';

    protected $fillable = [
        'documento_tipo_codigo',
        'numero_documento',
        'razon_social',
        'direccion',
        'telefono',
        'email',
    ];

    public function documentoTipo()
    {
        return $this->belongsTo(DocumentoTipo::class, 'documento_tipo_codigo', 'codigo');
    }
}
