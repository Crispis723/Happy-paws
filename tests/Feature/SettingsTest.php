<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Setting;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_cita_price()
    {
        $this->seed(); // seed roles and settings
        $admin = User::where('email', 'admin@prueba.com')->first();

        $this->actingAs($admin)
             ->post(route('admin.settings.cita_precio.update'), ['cita_precio' => 45.00])
             ->assertRedirect(route('admin.settings.cita_precio.edit'));

        $this->assertEquals('45.00', Setting::get('cita_precio'));
    }

    public function test_non_admin_cannot_access_price_edit()
    {
        $user = User::factory()->create();
        $this->actingAs($user)
             ->get(route('admin.settings.cita_precio.edit'))
             ->assertStatus(403);
    }
}
