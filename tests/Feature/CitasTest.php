<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Cita;

class CitasTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_citas_index()
    {
        $response = $this->get('/citas');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_create_cita()
    {
        $user = User::factory()->create();
        $mascota = \App\Models\Mascota::factory()->create(['user_id' => $user->id]);

        \Spatie\Permission\Models\Role::findOrCreate('veterinario', 'web');
        $vet = \App\Models\User::factory()->create();
        $vet->assignRole('veterinario');

        $payload = [
            'fecha_hora' => now()->addDay()->format('Y-m-d H:i'),
            'mascota_id' => $mascota->id,
            'veterinario_id' => $vet->id,
            'motivo' => 'Chequeo',
        ];

        $response = $this->actingAs($user)->post('/citas', $payload);
        $response->assertRedirect('/citas');

        $this->assertDatabaseHas('citas', [
            'cliente_nombre' => $user->name,
            'mascota_id' => $mascota->id,
        ]);
    }

    public function test_authenticated_user_can_update_and_delete_cita()
    {
        $user = User::factory()->create();
        $cita = Cita::create([
            'fecha_hora' => now()->addDay()->format('Y-m-d H:i'),
            'cliente_nombre' => 'Antes',
            'cliente_telefono' => '444333222',
            'mascota_nombre' => 'Old',
            'mascota_especie' => 'Perro',
            'motivo' => 'Previo',
            'estado' => 'pendiente',
        ]);

        $updatePayload = [
            'fecha_hora' => now()->addDays(2)->format('Y-m-d H:i'),
            'cliente_nombre' => 'Después',
            'cliente_telefono' => '444333222',
            'mascota_nombre' => 'New',
            'mascota_especie' => 'Perro',
            'motivo' => 'Actualizado',
            'estado' => 'confirmada',
        ];

        $this->actingAs($user)->put("/citas/{$cita->id}", $updatePayload)
            ->assertRedirect('/citas');

        $this->assertDatabaseHas('citas', ['cliente_nombre' => 'Después']);

        $this->actingAs($user)->delete("/citas/{$cita->id}")
            ->assertRedirect('/citas');

        $this->assertDatabaseMissing('citas', ['id' => $cita->id]);
    }
}
