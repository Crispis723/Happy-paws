<?php

namespace App\Policies;

use App\Models\Cita;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CitaPolicy
{
    /**
     * Ver lista de citas.
     * 
     * ACCESO:
     * - Admin: Sí
     * - Veterinario: Solo citas asignadas a él
     * - Recepcionista: Sí (todas)
     * - Gerente: Sí (todas)
     * - Public: Sus propias citas
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || 
               $user->isStaffType('recepcionista') || 
               $user->isStaffType('gerente') ||
               $user->isStaffType('veterinario') ||
               $user->isPublic();
    }

    /**
     * Ver una cita específica.
     */
    public function view(User $user, Cita $cita): bool
    {
        // Admin y recepcionista/gerente ven todas
        if ($user->isAdmin() || $user->isStaffType('recepcionista') || $user->isStaffType('gerente')) {
            return true;
        }

        // Veterinario solo las suyas (asignadas)
        if ($user->isStaffType('veterinario')) {
            return $cita->veterinario_id === $user->id;
        }

        // Public solo sus propias citas
        if ($user->isPublic()) {
            return $cita->user_id === $user->id || $cita->cliente_nombre === $user->name;
        }

        return false;
    }

    /**
     * Crear cita.
     * - Public: Sí
     * - Staff: Recepcionista y gerente
     */
    public function create(User $user): bool
    {
        return $user->isPublic() || 
               $user->isStaffType('recepcionista') || 
               $user->isStaffType('gerente') ||
               $user->isAdmin();
    }

    /**
     * Actualizar cita.
     */
    public function update(User $user, Cita $cita): bool
    {
        // Admin siempre
        if ($user->isAdmin()) {
            return true;
        }

        // Recepcionista/gerente pueden editar
        if ($user->isStaffType('recepcionista') || $user->isStaffType('gerente')) {
            return true;
        }

        // Public solo sus citas
        if ($user->isPublic()) {
            return $cita->user_id === $user->id;
        }

        return false;
    }

    /**
     * Eliminar cita - solo gerente y admin.
     */
    public function delete(User $user, Cita $cita): bool
    {
        return $user->isAdmin() || $user->isStaffType('gerente');
    }

    /**
     * Veterinario NO puede acceder a facturación de citas.
     */
    public function viewBilling(User $user): bool
    {
        return !$user->isStaffType('veterinario');
    }
}
