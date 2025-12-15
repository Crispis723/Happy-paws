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
     * 
     * Crea roles, permisos y usuarios de prueba.
     * 
     * USUARIOS DE PRUEBA:
     * - Admin: admin@happypaws.test / admin123
     * - Contador: contador@happypaws.test / contador123
     * - Veterinario: vet@happypaws.test / vet123
     * - Asistente: asistente@happypaws.test / asistente123
     * - Gerente: gerente@happypaws.test / gerente123
     * - Cliente: cliente@happypaws.test / cliente123
     */
    public function run(): void
    {
        // 🔐 CREAR ROLES PRINCIPALES
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'public', 'guard_name' => 'web']);
        
        // 🔐 CREAR ROLES/CATEGORÍAS PARA STAFF
        Role::firstOrCreate(['name' => 'contador', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'veterinario', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'recepcionista', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'gerente', 'guard_name' => 'web']);

        // 🔐 CREAR PERMISOS MODULARES (DASHBOARD TIPO ODOO)
        $modulePermissions = [
            // Módulos principales
            'module-clientes' => 'Acceso al módulo de Clientes',
            'module-mascotas' => 'Acceso al módulo de Mascotas',
            'module-citas' => 'Acceso al módulo de Citas',
            'module-productos' => 'Acceso al módulo de Productos',
            'module-compras' => 'Acceso al módulo de Compras',
            'module-ventas' => 'Acceso al módulo de Ventas',
            'module-reportes-financieros' => 'Acceso a Reportes Financieros',
            'module-reportes-medicos' => 'Acceso a Reportes Médicos',
            'module-configuracion' => 'Acceso a Configuración',
            
            // Permisos de administración
            'manage-users' => 'Gestionar usuarios',
            'manage-roles' => 'Gestionar roles y permisos',
            'manage-settings' => 'Gestionar configuración del sistema',
            'settings_manage' => 'Gestionar configuración (legacy)',
        ];

        foreach ($modulePermissions as $name => $description) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // 👤 CREAR USUARIOS DE PRUEBA
        $this->createExampleUsers();

        // 🎯 ASIGNAR PERMISOS SEGÚN CATEGORÍA
        $this->assignPermissionsByStaffType($adminRole);
    }

    /**
     * Crear usuarios de prueba con contraseñas simples.
     * 
     * ⚠️ NO usar en producción
     */
    protected function createExampleUsers(): void
    {
        // 🔑 ADMIN #1 - Superadministrador
        $admin = User::firstOrCreate(
            ['email' => 'admin@happypaws.test'],
            [
                'name' => 'Juan Administrador',
                'password' => Hash::make('admin123'),
                'user_type' => 'admin',
                'staff_type' => null,
                'activo' => true,
                'telefono' => '999000001'
            ]
        );
        $admin->assignRole('admin');

        // 🔑 ADMIN #2 - Para tests
        $adminTest = User::firstOrCreate(
            ['email' => 'admin@prueba.com'],
            [
                'name' => 'Admin de Pruebas',
                'password' => Hash::make('admin123'),
                'user_type' => 'admin',
                'staff_type' => null,
                'activo' => true,
                'telefono' => '999000000'
            ]
        );
        $adminTest->assignRole('admin');

        // 💼 CONTADOR - Facturación y reportes
        $contador = User::firstOrCreate(
            ['email' => 'contador@happypaws.test'],
            [
                'name' => 'Carlos Contador',
                'password' => Hash::make('contador123'),
                'user_type' => 'staff',
                'staff_type' => 'contador',
                'activo' => true,
                'telefono' => '999000002'
            ]
        );
        $contador->assignRole('staff');
        $contador->assignRole('contador');

        // 🏥 VETERINARIO - Citas y mascotas
        $vet = User::firstOrCreate(
            ['email' => 'vet@happypaws.test'],
            [
                'name' => 'Dra. María Veterinaria',
                'password' => Hash::make('vet123'),
                'user_type' => 'staff',
                'staff_type' => 'veterinario',
                'activo' => true,
                'telefono' => '999000003'
            ]
        );
        $vet->assignRole('staff');
        $vet->assignRole('veterinario');

        // 📞 RECEPCIONISTA / ASISTENTE - Clientes, mascotas y citas
        $recep = User::firstOrCreate(
            ['email' => 'asistente@happypaws.test'],
            [
                'name' => 'Laura Asistente',
                'password' => Hash::make('asistente123'),
                'user_type' => 'staff',
                'staff_type' => 'recepcionista',
                'activo' => true,
                'telefono' => '999000004'
            ]
        );
        $recep->assignRole('staff');
        $recep->assignRole('recepcionista');

        // 👔 GERENTE - Acceso operativo completo
        $gerente = User::firstOrCreate(
            ['email' => 'gerente@happypaws.test'],
            [
                'name' => 'Juan Gerente',
                'password' => Hash::make('gerente123'),
                'user_type' => 'staff',
                'staff_type' => 'gerente',
                'activo' => true,
                'telefono' => '999000005'
            ]
        );
        $gerente->assignRole('staff');
        $gerente->assignRole('gerente');

        // 👥 CLIENTE / PUBLIC - Usuario público/cliente
        $public = User::firstOrCreate(
            ['email' => 'cliente@happypaws.test'],
            [
                'name' => 'Roberto Cliente',
                'password' => Hash::make('cliente123'),
                'user_type' => 'public',
                'staff_type' => null,
                'activo' => true,
                'telefono' => '999999999'
            ]
        );
        $public->assignRole('public');
    }

    /**
     * Asignar permisos modulares según categoría de staff.
     */
    protected function assignPermissionsByStaffType($adminRole): void
    {
        // 🔑 ADMIN: Acceso total
        $adminRole->givePermissionTo(Permission::all());

        // 💼 CONTADOR: Facturación y reportes financieros
        $contadorRole = Role::findByName('contador');
        $contadorRole->givePermissionTo([
            'module-clientes',
            'module-productos',
            'module-compras',
            'module-ventas',
            'module-reportes-financieros',
        ]);

        // 🏥 VETERINARIO: Historial médico y citas
        $veterinarioRole = Role::findByName('veterinario');
        $veterinarioRole->givePermissionTo([
            'module-clientes',
            'module-mascotas',
            'module-citas',
            'module-reportes-medicos',
        ]);

        // 📞 RECEPCIONISTA: Clientes, mascotas y citas
        $recepcionistaRole = Role::findByName('recepcionista');
        $recepcionistaRole->givePermissionTo([
            'module-clientes',
            'module-mascotas',
            'module-citas',
        ]);

        // 👔 GERENTE: Acceso operativo completo (sin configuración de sistema)
        $gerenteRole = Role::findByName('gerente');
        $gerenteRole->givePermissionTo([
            'module-clientes',
            'module-mascotas',
            'module-citas',
            'module-productos',
            'module-compras',
            'module-ventas',
            'module-reportes-financieros',
            'module-reportes-medicos',
        ]);

        // Permiso especial de settings para admin
        $settingsManage = Permission::findByName('settings_manage');
        $adminRole->givePermissionTo($settingsManage);
    }
}
