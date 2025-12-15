<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Mascota;

class MascotaSeeder extends Seeder {
  public function run(){
    $user = User::first() ?? User::factory()->create(['email'=>'dueno@example.com','password'=>bcrypt('password')]);
    Mascota::create(['user_id'=>$user->id,'nombre'=>'Firulais','especie'=>'Perro','raza'=>'Labrador']);
    Mascota::create(['user_id'=>$user->id,'nombre'=>'Michi','especie'=>'Gato','raza'=>'Siames']);
  }
}