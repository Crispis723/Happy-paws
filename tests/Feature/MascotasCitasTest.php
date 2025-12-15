<?php

namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Mascota;
use App\Models\Cita;

class MascotasCitasTest extends TestCase {
  use RefreshDatabase;

  public function test_usuario_auth_puede_crear_mascota(){
    $user = User::factory()->create();
    $this->actingAs($user)
         ->post('/mascotas', [
            'nombre'=>'Rex','especie'=>'Perro'
         ])->assertRedirect('/mascotas');
    $this->assertDatabaseHas('mascotas',['nombre'=>'Rex','user_id'=>$user->id]);
  }

  public function test_usuario_auth_puede_pedir_cita_con_mascota(){
    $user = User::factory()->create();
    $mascota = Mascota::factory()->create(['user_id'=>$user->id]);
    $payload = [
      'fecha_hora' => now()->addDay()->format('Y-m-d H:i'),
      'motivo' => 'Consulta',
      'mascota_id' => $mascota->id
    ];
    $this->actingAs($user)->post('/citas', $payload)
         ->assertRedirect('/citas');
    $this->assertDatabaseHas('citas',['mascota_id'=>$mascota->id,'motivo'=>'Consulta']);
  }

  public function test_guest_puede_pedir_cita_sin_mascota(){
    $payload = [
      'fecha_hora' => now()->addDay()->format('Y-m-d H:i'),
      'cliente_nombre' => 'Invitado',
      'cliente_telefono' => '999999999',
      'mascota_nombre' => 'Otro',
      'mascota_especie' => 'Perro',
      'motivo' => 'Consulta'
    ];
    $this->post('/citas', $payload)->assertRedirect('/');
    $this->assertDatabaseHas('citas',['cliente_nombre'=>'Invitado','mascota_nombre'=>'Otro']);
  }
}