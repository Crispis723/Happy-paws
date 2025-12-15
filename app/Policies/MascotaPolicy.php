<?php

namespace App\Policies;

use App\Models\Mascota;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MascotaPolicy
{
    /**
     * Ver mascotas - recepcionista, gerente y admin.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || 
               $user->isStaffType('recepcionista') || 
               $user->isStaffType('gerente') ||
               $user->isPublic();
    }

    /**
     * Ver una mascota específica.
     */
    public function view(User $user, Mascota $mascota): bool
    {
        // Admin y staff pueden ver cualquiera
        if ($user->isAdmin() || $user->isStaffType('recepcionista') || $user->isStaffType('gerente')) {
            return true;
        }

        // Usuarios públicos solo ven sus propias mascotas
        return $user->isPublic() && $mascota->user_id === $user->id;
    }

    /**
     * Crear mascotas.
     */
    public function create(User $user): bool
    {
        // Cualquiera puede crear mascotas
        return true;
    }

    /**
     * Actualizar mascota.
     */
    public function update(User $user, Mascota $mascota): bool
    {
        // Admin y recepcionista/gerente pueden editar
        if ($user->isAdmin() || $user->isStaffType('recepcionista') || $user->isStaffType('gerente')) {
            return true;
        }

        // Usuarios públicos solo sus mascotas
        return $user->isPublic() && $mascota->user_id === $user->id;
    }

    /**
     * Eliminar mascota - solo admin y gerente.
     */
    public function delete(User $user, Mascota $mascota): bool
    {
        if ($user->isAdmin() || $user->isStaffType('gerente')) {
            return true;
        }

        return $user->isPublic() && $mascota->user_id === $user->id;
    }

    /**
     * Contador NO puede ver mascotas.
     */
    public function viewCounter(User $user): bool
    {
        return !$user->isStaffType('contador');
    }
}
