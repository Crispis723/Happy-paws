<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClientePolicy
{
    /**
     * Determina si el usuario puede ver la lista de clientes.
     * 
     * ACCESO:
     * - Admin: Sí
     * - Recepcionista: Sí
     * - Gerente: Sí
     * - Otros: No
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || 
               $user->isStaffType('recepcionista') || 
               $user->isStaffType('gerente');
    }

    /**
     * Determina si el usuario puede ver un cliente específico.
     */
    public function view(User $user, Cliente $cliente): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determina si el usuario puede crear clientes.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || 
               $user->isStaffType('recepcionista') || 
               $user->isStaffType('gerente');
    }

    /**
     * Determina si el usuario puede actualizar un cliente.
     */
    public function update(User $user, Cliente $cliente): bool
    {
        return $user->isAdmin() || 
               $user->isStaffType('recepcionista') || 
               $user->isStaffType('gerente');
    }

    /**
     * Determina si el usuario puede eliminar un cliente.
     * Solo admin y gerente.
     */
    public function delete(User $user, Cliente $cliente): bool
    {
        return $user->isAdmin() || $user->isStaffType('gerente');
    }

    /**
     * Restaurar clientes eliminados - solo admin.
     */
    public function restore(User $user, Cliente $cliente): bool
    {
        return $user->isAdmin();
    }

    /**
     * Eliminar permanentemente - solo admin.
     */
    public function forceDelete(User $user, Cliente $cliente): bool
    {
        return $user->isAdmin();
    }
}
