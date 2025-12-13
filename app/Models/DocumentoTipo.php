<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoTipo extends Model
{
    protected $table = 'documento_tipos';
    protected $primaryKey = 'codigo';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'codigo',
        'descripcion',
    ];

    public $timestamps = false;

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'documento_tipo_codigo', 'codigo');
    }
}
