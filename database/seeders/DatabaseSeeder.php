<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UnidadSeeder::class);
        $this->call(AfectacionSeeder::class);
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(CitaSeeder::class);
        $this->call(MascotaSeeder::class);
        $this->call(SettingsSeeder::class);

        // Test user for manual UI testing
        \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Usuario Prueba', 'password' => bcrypt('password')]
        );
    }
}
