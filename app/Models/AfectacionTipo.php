<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AfectacionTipo extends Model
{
    protected $table = 'afectacion_tipos';
    protected $primaryKey = 'codigo';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'letra',
        'porcentaje'
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'afectacion_tipo_codigo', 'codigo');
    }
}
