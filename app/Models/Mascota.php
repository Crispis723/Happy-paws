<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mascota extends Model
{
   protected $fillable = ['user_id','nombre','especie','raza','sexo','fecha_nacimiento','notas']; 
   public function user() { return $this->belongsTo(User::class); }
   public function citas() { return $this->hasMany(Cita::class); }
}
