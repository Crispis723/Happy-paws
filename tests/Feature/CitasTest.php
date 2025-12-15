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

        $payload = [
            'fecha_hora' => now()->addDay()->format('Y-m-d H:i'),
            'cliente_nombre' => 'Test Cliente',
            'cliente_telefono' => '999888777',
            'mascota_nombre' => 'Buddy',
            'mascota_especie' => 'Perro',
            'motivo' => 'Chequeo',
            'precio' => 30.00,
        ];

        $response = $this->actingAs($user)->post('/citas', $payload);
        $response->assertRedirect('/citas');

        $this->assertDatabaseHas('citas', [
            'cliente_nombre' => 'Test Cliente',
            'mascota_nombre' => 'Buddy',
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
