<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Cita extends Model
{
    protected $fillable = [
        'mascota_id',
        'mascota_nombre',
        'mascota_especie',
        'fecha_hora',
        'cliente_nombre',
        'cliente_telefono',
        'mascota_nombre',
        'mascota_especie',
        'motivo',
        'estado',
        'precio',
        'notas',
        'veterinario_id',
    ];

    protected $dates = [
        'fecha_hora',
        'created_at',
        'updated_at',
    ];

    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }

    public function veterinario()
    {
        return $this->belongsTo(User::class, 'veterinario_id');
    }
}