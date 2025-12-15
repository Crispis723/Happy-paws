<?php

namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Mascota;
use App\Models\Cita;

class MascotasCitasTest extends TestCase
{
    use RefreshDatabase;

  public function test_usuario_auth_puede_crear_mascota(){
    $user = User::factory()->create();
    $this->actingAs($user)
         ->post('/mascotas', [
            'nombre'=>'Rex','especie'=>'Perro'
         ])->assertRedirect('/mascotas');
    $this->assertDatabaseHas('mascotas',['nombre'=>'Rex','user_id'=>$user->id]);
  }

  public function test_usuario_auth_sin_mascotas_es_redirigido_a_crear_mascota_para_pedir_cita(){
    $user = User::factory()->create();
    $this->actingAs($user)->get('/citas/create')
         ->assertRedirect('/mascotas/create');
  }

  public function test_usuario_auth_sin_mascotas_no_puede_enviar_cita_sin_mascota(){
    $user = User::factory()->create();
    $payload = [
      'fecha_hora' => now()->addDay()->format('Y-m-d H:i'),
      'motivo' => 'Consulta'
    ];

    $this->actingAs($user)->post('/citas', $payload)
         ->assertRedirect('/mascotas/create');
  }

  public function test_usuario_auth_puede_pedir_cita_con_mascota(){
    $user = User::factory()->create();
    $mascota = Mascota::factory()->create(['user_id'=>$user->id]);
    \Spatie\Permission\Models\Role::findOrCreate('veterinario', 'web');
    $vet = \App\Models\User::factory()->create();
    $vet->assignRole('veterinario');
    $payload = [
      'fecha_hora' => now()->addDay()->format('Y-m-d H:i'),
      'motivo' => 'Consulta',
      'mascota_id' => $mascota->id,
      'veterinario_id' => $vet->id,
    ];

    $this->actingAs($user)->post('/citas', $payload)
         ->assertRedirect('/citas');
  }

  public function test_invitado_puede_pedir_cita_con_datos_manual_y_veterinario(){
    \Spatie\Permission\Models\Role::findOrCreate('veterinario', 'web');
    $vet = \App\Models\User::factory()->create();
    $vet->assignRole('veterinario');
    $payload = [
      'fecha_hora' => now()->addDays(2)->format('Y-m-d H:i'),
      'cliente_nombre' => 'Invitado',
      'cliente_telefono' => '999999999',
      'mascota_nombre' => 'Otro',
      'mascota_especie' => 'Perro',
      'motivo' => 'Consulta',
      'veterinario_id' => $vet->id,
    ];
    $this->post('/citas', $payload)->assertRedirect('/');
    $this->assertDatabaseHas('citas',[
      'cliente_nombre'=>'Invitado',
      'mascota_nombre'=>'Otro',
      'veterinario_id' => $vet->id,
    ]);
  }
}