<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'staff_type',
        'activo',
        'telefono'
    ];

    // ============ RELACIONES ============
    
    public function mascotas() 
    { 
        return $this->hasMany(Mascota::class); 
    }

    // ============ MÉTODOS HELPER ============

    /**
     * Determina si el usuario es administrador.
     */
    public function isAdmin(): bool
    {
        return $this->user_type === 'admin';
    }

    /**
     * Determina si el usuario es empleado.
     */
    public function isStaff(): bool
    {
        return $this->user_type === 'staff';
    }

    /**
     * Determina si el usuario es público.
     */
    public function isPublic(): bool
    {
        return $this->user_type === 'public';
    }

    /**
     * Verifica si el empleado es de una categoría específica.
     * 
     * @param string $type contador|veterinario|recepcionista|gerente
     */
    public function isStaffType(string $type): bool
    {
        return $this->isStaff() && $this->staff_type === $type;
    }

    /**
     * Obtiene todas las categorías de empleado permitidas.
     */
    public static function staffTypes(): array
    {
        return ['contador', 'veterinario', 'recepcionista', 'gerente'];
    }

    /**
     * Solo staff con ciertos tipos pueden acceder a facturación.
     */
    public function canAccessBilling(): bool
    {
        return $this->isAdmin() || 
               $this->isStaffType('contador') || 
               $this->isStaffType('gerente');
    }

    /**
     * Solo veterinarios pueden acceder a historiales médicos.
     */
    public function canAccessMedical(): bool
    {
        return $this->isAdmin() || $this->isStaffType('veterinario');
    }

    /**
     * Recepcionistas y gerentes pueden gestionar citas.
     */
    public function canManageCitas(): bool
    {
        return $this->isAdmin() || 
               $this->isStaffType('recepcionista') || 
               $this->isStaffType('gerente');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
