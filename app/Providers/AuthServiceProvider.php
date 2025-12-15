<?php

namespace App\Providers;

use App\Models\Cliente;
use App\Models\Mascota;
use App\Models\Cita;
use App\Models\Venta;
use App\Models\User;
use App\Policies\ClientePolicy;
use App\Policies\MascotaPolicy;
use App\Policies\CitaPolicy;
use App\Policies\VentaPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Las políticas de autorización mapeadas por modelo.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Cliente::class => ClientePolicy::class,
        Mascota::class => MascotaPolicy::class,
        Cita::class => CitaPolicy::class,
        Venta::class => VentaPolicy::class,
    ];

    /**
     * Registra los servicios de autorización de la aplicación.
     */
    public function boot(): void
    {
        // Gates personalizados (alternativa a Policies para lógica simple)
        
        /**
         * Gate: Admin absoluto
         * Se usa: Gate::authorize('admin')
         */
        Gate::define('admin', function (User $user) {
            return $user->isAdmin();
        });

        /**
         * Gate: Es empleado
         * Se usa: Gate::authorize('staff')
         */
        Gate::define('staff', function (User $user) {
            return $user->isStaff() || $user->isAdmin();
        });

        /**
         * Gate: Puede acceder a facturación
         * Se usa: Gate::authorize('access-billing')
         */
        Gate::define('access-billing', function (User $user) {
            return $user->canAccessBilling();
        });

        /**
         * Gate: Puede acceder a historiales médicos
         * Se usa: Gate::authorize('access-medical')
         */
        Gate::define('access-medical', function (User $user) {
            return $user->canAccessMedical();
        });

        /**
         * Gate: Puede gestionar citas
         * Se usa: Gate::authorize('manage-citas')
         */
        Gate::define('manage-citas', function (User $user) {
            return $user->canManageCitas();
        });

        /**
         * Gate: Solo admin puede crear staff
         */
        Gate::define('create-staff', function (User $user) {
            return $user->isAdmin();
        });

        /**
         * Gate: Solo admin puede editar roles
         */
        Gate::define('edit-roles', function (User $user) {
            return $user->isAdmin();
        });
    }
}
