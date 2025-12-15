<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tables = [
            'unidades',
            'afectacion_tipos',
            'clientes',
            'documento_tipos',
            'productos',
            'ventas',
            'comprobante_tipos',
            'users',
            'roles_permisos',
            'compras',
            'proveedores'
        ];
        $actions = ['list', 'create', 'edit', 'delete'];
        $permissions = [];

        // Crear permisos con formato tabla_accion (ej. ventas_list)
        foreach ($tables as $table) {
            foreach ($actions as $action) {
                $permName = "{$table}_{$action}";
                $permissions[] = Permission::firstOrCreate(['name' => $permName]);
            }
        }

        // Crear roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $vendedorRole = Role::firstOrCreate(['name' => 'vendedor']);
        $veterinarioRole = Role::firstOrCreate(['name' => 'veterinario']);

        // Asignar todos los permisos al rol admin
        $adminRole->syncPermissions($permissions);

        // Permiso específico para administrar configuraciones (precio de cita)
        $settingsPerm = Permission::firstOrCreate(['name' => 'settings_manage']);
        $adminRole->givePermissionTo($settingsPerm);

        // Permisos solo de ventas para vendedor
        $vendedorPerms = [
            'ventas_list',
            'ventas_create',
            'ventas_edit',
            'ventas_delete',
        ];
        $vendedorRole->syncPermissions($vendedorPerms);

        // Crear usuario administrador
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@prueba.com'],
            [
                'name' => 'Juan Carlos (Administrador)',
                'password' => Hash::make('admin'), // Cambiar por una segura en producción
            ]
        );

        $adminUser->assignRole($adminRole);
    }
}
