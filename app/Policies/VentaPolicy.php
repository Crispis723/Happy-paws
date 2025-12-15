<?php

namespace App\Policies;

use App\Models\Venta;
use App\Models\User;

class VentaPolicy
{
    /**
     * Ver ventas - solo usuarios con acceso a facturación.
     * 
     * ACCESO:
     * - Admin: Sí
     * - Contador: Sí
     * - Gerente: Sí
     * - Otros: No
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || 
               $user->isStaffType('contador') || 
               $user->isStaffType('gerente');
    }

    /**
     * Ver una venta específica.
     */
    public function view(User $user, Venta $venta): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Crear venta.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || 
               $user->isStaffType('contador') || 
               $user->isStaffType('gerente');
    }

    /**
     * Actualizar venta.
     */
    public function update(User $user, Venta $venta): bool
    {
        return $user->isAdmin() || 
               $user->isStaffType('contador') || 
               $user->isStaffType('gerente');
    }

    /**
     * Eliminar venta - solo admin y contador con permiso.
     */
    public function delete(User $user, Venta $venta): bool
    {
        return $user->isAdmin() || $user->isStaffType('contador');
    }

    /**
     * Veterinario NO puede acceder a ventas.
     */
    public function viewAsVeterinary(User $user): bool
    {
        return !$user->isStaffType('veterinario');
    }

    /**
     * Recepcionista NO puede acceder a ventas.
     */
    public function viewAsReceptionist(User $user): bool
    {
        return !$user->isStaffType('recepcionista');
    }
}
