# 🔐 SISTEMA DE AUTENTICACIÓN Y AUTORIZACIÓN - GUÍA COMPLETA

## 📑 ÍNDICE
1. Arquitectura General
2. Base de Datos
3. Modelos y Relaciones
4. Autenticación
5. Autorización (Policies y Gates)
6. Rutas Protegidas
7. Middleware
8. Vistas Dinámicas
9. Ejemplos Prácticos
10. Mejores Prácticas y Seguridad

---

## 1️⃣ ARQUITECTURA GENERAL

### Tipos de Usuarios

```
┌─────────────────────────────────────┐
│        USUARIO (user_type)          │
├─────────────────────────────────────┤
│                                     │
├─ ADMIN                             │ (1 persona)
│  └─ Acceso completo                │
│  └─ Gestiona usuarios, roles, config
│                                     │
├─ STAFF (empleados)                 │ (N personas)
│  ├─ Contador                       │ → Facturación
│  ├─ Veterinario                    │ → Historiales médicos
│  ├─ Recepcionista                  │ → Gestión de citas/clientes
│  └─ Gerente                        │ → Acceso operativo completo
│                                     │
└─ PUBLIC (clientes)                 │ (∞ personas)
   ├─ Ver/crear mascotas             │
   └─ Pedir citas                    │
```

### Flujo de Autenticación y Autorización

```
LOGIN (email + password)
     ↓
Auth::attempt() ✓
     ↓
Obtiene user_type de BD
     ↓
Redirecciona según tipo
     ├─ Admin/Staff → /admin/dashboard
     └─ Public → /public/dashboard
         ↓
    El usuario intenta acceder a una ruta/acción
         ↓
    ¿Middleware lo permite? → Sí
         ↓
    ¿Policy/Gate lo autoriza? → Sí
         ↓
    Acción ejecutada ✓
```

---

## 2️⃣ BASE DE DATOS

### Tabla Users - Nuevos Campos

```sql
-- Ejecutar: php artisan migrate

ALTER TABLE users ADD COLUMN user_type 
    ENUM('admin', 'staff', 'public') 
    DEFAULT 'public';

ALTER TABLE users ADD COLUMN staff_type 
    ENUM('contador', 'veterinario', 'recepcionista', 'gerente') 
    NULL;

ALTER TABLE users ADD COLUMN activo BOOLEAN DEFAULT true;
ALTER TABLE users ADD COLUMN telefono VARCHAR(20) NULL;

-- Índices para performance
ALTER TABLE users ADD INDEX idx_user_type (user_type);
ALTER TABLE users ADD INDEX idx_staff_type (staff_type);
ALTER TABLE users ADD INDEX idx_activo (activo);
```

### Ejemplos de Datos

```sql
-- Admin
INSERT INTO users VALUES (
    1, 'admin@clinic.test', 'Admin', pwd_hash, 
    'admin', NULL, true, '999-0001'
);

-- Contador
INSERT INTO users VALUES (
    2, 'contador@clinic.test', 'Carlos', pwd_hash,
    'staff', 'contador', true, '999-0002'
);

-- Veterinario
INSERT INTO users VALUES (
    3, 'vet@clinic.test', 'Dr. Vet', pwd_hash,
    'staff', 'veterinario', true, '999-0003'
);

-- Cliente
INSERT INTO users VALUES (
    4, 'cliente@example.test', 'Juan', pwd_hash,
    'public', NULL, true, '999-0099'
);
```

---

## 3️⃣ MODELOS Y RELACIONES

### User Model

```php
class User extends Authenticatable {
    use HasFactory, HasRoles;
    
    protected $fillable = [
        'name', 'email', 'password',
        'user_type', 'staff_type', 'activo', 'telefono'
    ];
    
    // ============ MÉTODOS HELPER ============
    
    public function isAdmin(): bool {
        return $this->user_type === 'admin';
    }
    
    public function isStaff(): bool {
        return $this->user_type === 'staff';
    }
    
    public function isPublic(): bool {
        return $this->user_type === 'public';
    }
    
    public function isStaffType(string $type): bool {
        return $this->isStaff() && $this->staff_type === $type;
    }
    
    // Checks de acceso rápido
    public function canAccessBilling(): bool {
        return $this->isAdmin() || 
               in_array($this->staff_type, ['contador', 'gerente']);
    }
    
    public function canAccessMedical(): bool {
        return $this->isAdmin() || $this->isStaffType('veterinario');
    }
    
    public function canManageCitas(): bool {
        return $this->isAdmin() || 
               in_array($this->staff_type, ['recepcionista', 'gerente']);
    }
}
```

---

## 4️⃣ AUTENTICACIÓN

### Login (AuthController@login)

```php
public function login(Request $request) {
    $validated = $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);
    
    if (Auth::attempt($validated)) {
        $user = Auth::user();
        
        // Verificar actividad
        if (!$user->activo) {
            Auth::logout();
            return back()->with('error', 'Cuenta inactiva');
        }
        
        // Redireccionar según tipo
        return $this->redirectByUserType($user);
    }
    
    return back()->with('error', 'Credenciales inválidas');
}

protected function redirectByUserType(User $user) {
    if ($user->isAdmin() || $user->isStaff()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('public.dashboard');
}
```

### Registro (AuthController@register)

```php
public function register(Request $request) {
    $validated = $request->validate([
        'name' => 'required|string',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed'
    ]);
    
    // ⚠️ SEGURIDAD: Siempre crear como 'public'
    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'user_type' => 'public',     // 🔐 Hardcoded
        'staff_type' => null,         // 🔐 No permitido
        'activo' => true,
    ]);
    
    Auth::login($user);
    return redirect()->route('public.dashboard');
}
```

**¿Por qué?** Staff y admin se crean SOLO desde admin panel, nunca desde registro público.

---

## 5️⃣ AUTORIZACIÓN - POLICIES Y GATES

### ¿Qué son Policies?

Las **Policies** son clases que centralizan la lógica de autorización para un modelo específico.

- ✅ Un archivo por modelo (ej. CitaPolicy)
- ✅ Métodos para cada acción (view, create, update, delete)
- ✅ Se registran en AuthServiceProvider
- ✅ Se usan en controladores y vistas

### Estructura de una Policy

```php
<?php
namespace App\Policies;

use App\Models\Cita;
use App\Models\User;

class CitaPolicy {
    /**
     * Determina quién puede ver la lista
     */
    public function viewAny(User $user): bool {
        return $user->isAdmin() || 
               $user->isStaffType('recepcionista') ||
               $user->isStaffType('gerente') ||
               $user->isStaffType('veterinario') ||
               $user->isPublic();
    }
    
    /**
     * Determina quién puede ver UNA cita específica
     */
    public function view(User $user, Cita $cita): bool {
        // Admin y recepcionista ven todas
        if ($user->isAdmin() || $user->isStaffType('recepcionista')) {
            return true;
        }
        
        // Vet solo sus citas asignadas
        if ($user->isStaffType('veterinario')) {
            return $cita->veterinario_id === $user->id;
        }
        
        // Public solo sus propias citas
        return $user->isPublic() && $cita->user_id === $user->id;
    }
    
    public function create(User $user): bool {
        return $user->isAdmin() || 
               $user->canManageCitas() ||
               $user->isPublic();
    }
    
    public function update(User $user, Cita $cita): bool {
        return $user->isAdmin() || 
               $user->isStaffType('recepcionista') ||
               ($user->isPublic() && $cita->user_id === $user->id);
    }
    
    public function delete(User $user, Cita $cita): bool {
        return $user->isAdmin() || $user->isStaffType('gerente');
    }
}
```

### Registrar Policies (AuthServiceProvider)

```php
<?php
namespace App\Providers;

use App\Models\{Cita, Venta, Mascota, Cliente};
use App\Policies\{CitaPolicy, VentaPolicy, MascotaPolicy, ClientePolicy};
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider {
    
    protected $policies = [
        Cita::class => CitaPolicy::class,
        Venta::class => VentaPolicy::class,
        Mascota::class => MascotaPolicy::class,
        Cliente::class => ClientePolicy::class,
    ];
    
    public function boot(): void {
        // Gates (lógica simple, reutilizable)
        
        Gate::define('admin', fn (User $user) => $user->isAdmin());
        
        Gate::define('access-billing', fn (User $user) => 
            $user->canAccessBilling()
        );
        
        Gate::define('manage-users', fn (User $user) => 
            $user->isAdmin()
        );
    }
}
```

### Usar Policies en Controladores

```php
class CitaController extends Controller {
    
    // Opción 1: Autorizar automáticamente en constructor
    public function __construct() {
        $this->authorizeResource(Cita::class, 'cita');
    }
    
    // Opción 2: Autorizar manualmente en métodos
    public function destroy(Cita $cita) {
        // Policy -> CitaPolicy@delete
        $this->authorize('delete', $cita);
        
        $cita->delete();
        return redirect()->route('citas.index');
    }
    
    // Opción 3: Usar Gates
    public function exportData() {
        Gate::authorize('export-data');
        // ejecutar export
    }
}
```

### Usar Policies en Vistas

```blade
{{-- Opción 1: Con @can (Policy) --}}
@can('delete', $cita)
    <button onclick="deleteCita({{ $cita->id }})">Eliminar</button>
@endcan

{{-- Opción 2: Con @gate (Gate) --}}
@gate('access-billing')
    <a href="{{ route('ventas.index') }}">Ventas</a>
@endgate

{{-- Opción 3: Con método helper en User --}}
@if(auth()->user()->canAccessMedical())
    <a href="{{ route('historiales.index') }}">Historiales</a>
@endif
```

---

## 6️⃣ RUTAS PROTEGIDAS

### Estructura de Rutas

```php
<?php
// routes/web.php

// Rutas públicas (sin autenticación)
Route::middleware('guest')->group(function () {
    Route::get('/', fn() => view('landing'))->name('landing');
    Route::get('login', fn() => view('auth.login'))->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// Citas públicas (invitados)
Route::get('citas/create', [CitaController::class, 'create'])->name('citas.create');
Route::post('citas', [CitaController::class, 'store'])->name('citas.store');

// ==================== ADMIN ====================
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');
    Route::resource('usuarios', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::get('admin/settings', [SettingController::class, 'index']);
});

// ==================== STAFF ====================
Route::middleware(['auth', 'staff'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);
    Route::resource('clientes', ClienteController::class);
});

// ==================== ESPECÍFICOS POR CATEGORÍA ====================

// Solo Contador y Gerente
Route::middleware(['auth', 'staff_type:contador,gerente'])->group(function () {
    Route::resource('ventas', VentaController::class);
    Route::resource('compras', CompraController::class);
    Route::get('reportes/financieros', [ReporteController::class, 'financial']);
});

// Solo Veterinarios
Route::middleware(['auth', 'staff_type:veterinario'])->group(function () {
    Route::get('citas/mis-citas', [CitaController::class, 'myAppointments']);
    Route::get('historiales/{mascota}', [HistorialController::class, 'show']);
});

// Recepcionista y Gerente
Route::middleware(['auth', 'staff_type:recepcionista,gerente'])->group(function () {
    Route::resource('citas', CitaController::class)->except(['create', 'store']);
});

// ==================== PUBLIC (Usuarios registrados) ====================
Route::middleware(['auth'])->group(function () {
    Route::get('/public/dashboard', [PublicDashboardController::class, 'index'])
        ->name('public.dashboard');
    Route::resource('mascotas', MascotaController::class);
    Route::get('mis-citas', [CitaController::class, 'myAppointments']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
```

---

## 7️⃣ MIDDLEWARE

### Tipos de Middleware

| Middleware | Propósito | Dónde |
|-----------|----------|-------|
| AdminMiddleware | Solo admin | Routes group |
| StaffMiddleware | Admin + Staff | Routes group |
| StaffTypeMiddleware | Categoría específica | Routes group |
| PublicMiddleware | Solo públicos | Login/register |

### AdminMiddleware

```php
<?php
namespace App\Http\Middleware;

use Closure;

class AdminMiddleware {
    public function handle($request, Closure $next) {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Solo administradores');
        }
        return $next($request);
    }
}
```

### StaffTypeMiddleware

```php
<?php
namespace App\Http\Middleware;

class StaffTypeMiddleware {
    public function handle($request, Closure $next, string $types) {
        $allowedTypes = array_map('trim', explode(',', $types));
        $user = auth()->user();
        
        if (!$user || (!$user->isAdmin() && !in_array($user->staff_type, $allowedTypes))) {
            abort(403, 'No tienes acceso');
        }
        
        return $next($request);
    }
}
```

### Registrar en bootstrap/app.php

```php
<?php
return Application::configure()
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'staff' => StaffMiddleware::class,
            'staff_type' => StaffTypeMiddleware::class,
        ]);
    })
    ->create();
```

---

## 8️⃣ VISTAS DINÁMICAS

### Dashboard Condicional

```blade
@if(auth()->user()->isAdmin())
    @include('dashboards.admin')
@elseif(auth()->user()->isStaff())
    @switch(auth()->user()->staff_type)
        @case('contador')
            @include('dashboards.contador')
            @break
        @case('veterinario')
            @include('dashboards.veterinario')
            @break
        @case('recepcionista')
            @include('dashboards.recepcionista')
            @break
        @case('gerente')
            @include('dashboards.gerente')
            @break
    @endswitch
@else
    @include('dashboards.public')
@endif
```

### Menú Dinámico

```blade
<nav>
    @if(auth()->user()->isAdmin())
        <a href="{{ route('usuarios.index') }}">Usuarios</a>
        <a href="{{ route('roles.index') }}">Roles</a>
    @endif
    
    @if(auth()->user()->canAccessBilling())
        <a href="{{ route('ventas.index') }}">Ventas</a>
    @endif
    
    @if(auth()->user()->canAccessMedical())
        <a href="{{ route('historiales.index') }}">Historiales</a>
    @endif
</nav>
```

---

## 9️⃣ EJEMPLOS PRÁCTICOS

### Ejemplo 1: Crear Cita (Público)

```php
// routes/web.php
Route::post('citas', [CitaController::class, 'store'])->name('citas.store');

// CitaController.php
public function store(Request $request) {
    // No requiere auth (público)
    // Policy se usa en store() si quieres validar
    
    $validated = $request->validate([...]);
    
    Cita::create($validated);
    return redirect('/')->with('success', 'Cita creada');
}

// En vista
<form action="{{ route('citas.store') }}" method="POST">
    @csrf
    <input type="text" name="cliente_nombre">
    <input type="datetime-local" name="fecha_hora">
    <button>Solicitar Cita</button>
</form>
```

### Ejemplo 2: Ver Cita (Protegido con Policy)

```php
// CitaController.php
public function __construct() {
    $this->authorizeResource(Cita::class, 'cita');
}

public function show(Cita $cita) {
    // Autorización automática por CitaPolicy@view
    return view('citas.show', compact('cita'));
}

// CitaPolicy.php
public function view(User $user, Cita $cita): bool {
    if ($user->isAdmin()) return true;
    if ($user->isStaffType('veterinario')) {
        return $cita->veterinario_id === $user->id;  // Solo sus citas
    }
    return $user->isPublic() && $cita->user_id === $user->id;  // Solo sus citas
}
```

### Ejemplo 3: Acceso Basado en Rol

```php
// Controller
public function exportFinancial() {
    // Solo contador y gerente
    Gate::authorize('access-billing');
    
    return Excel::download(new VentasExport(), 'ventas.xlsx');
}

// Vista
@gate('access-billing')
    <a href="{{ route('ventas.export') }}" class="btn btn-primary">
        Exportar
    </a>
@endgate
```

---

## 🔟 MEJORES PRÁCTICAS Y SEGURIDAD

### 1. Validación en Múltiples Capas

```php
// Capa 1: Middleware (acceso básico)
Route::middleware(['auth', 'staff_type:contador'])->group(...);

// Capa 2: Policy (acceso granular)
public function update(Venta $venta) {
    $this->authorize('update', $venta);
    // ...
}

// Capa 3: Validación (datos específicos)
$validated = $request->validate(['monto' => 'numeric|min:0']);
```

### 2. Nunca Confiar en Input del Usuario

```php
// ❌ MAL
$user = User::create([
    'email' => $request->email,
    'user_type' => $request->user_type,  // ¡Usuario puede poner 'admin'!
    'staff_type' => $request->staff_type,
]);

// ✅ BIEN
$user = User::create([
    'email' => $request->email,
    'user_type' => 'public',  // Hardcoded
    'staff_type' => null,      // Hardcoded
]);

// Para staff, validar en admin:
if (auth()->user()->isAdmin()) {
    $user->staff_type = $request->validated()['staff_type'];
}
```

### 3. Auditoría

```php
// Registrar cambios importantes
public function destroy(User $user) {
    // Admin puede eliminar
    $this->authorize('delete', $user);
    
    \Log::warning("Usuario eliminado: {$user->email} por " . auth()->user()->email);
    
    $user->delete();
}
```

### 4. Rate Limiting

```php
// Proteger login contra fuerza bruta
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');  // 5 intentos por minuto
```

### 5. Contraseñas Seguras

```php
// En seeder/factory
'password' => Hash::make('password123')  // Siempre hasheada

// Validación
'password' => 'required|min:8|regex:/[A-Z]/|regex:/[0-9]/'
```

### 6. Tokens CSRF

```blade
{{-- En todos los formularios --}}
<form method="POST">
    @csrf
    {{-- ... --}}
</form>
```

### 7. Sanitizar Datos

```php
// En vistas
{{ $user->email }}  // Automáticamente escapado
{!! $user->bio !!}   // Solo si confías (HTML permitido)
```

---

## 📊 MATRIZ DE PERMISOS

```
                    Admin  Contador  Vet  Recepción  Gerente  Public
────────────────────────────────────────────────────────────────────
Ver Usuarios          ✓
Crear Usuarios        ✓                              ✓
Editar Usuarios       ✓                              ✓
Ver Facturación       ✓      ✓                       ✓
Ver Mascotas          ✓      ✗       ✓       ✓       ✓        ✓(*)
Crear Mascotas        ✓              ✓       ✓       ✓        ✓
Ver Citas             ✓              ✓(**)   ✓       ✓        ✓(***)
Crear Citas           ✓              ✓       ✓       ✓        ✓
Ver Historiales       ✓              ✓
Modificar Historiales ✓              ✓
────────────────────────────────────────────────────────────────────
(*)    Solo sus mascotas
(**)   Solo citas asignadas
(***)  Solo sus citas
```

---

## 🚀 CHECKLIST DE SEGURIDAD

- [ ] Usuarios no pueden elegir rol en registro
- [ ] Staff/admin solo creables por admin
- [ ] Contraseñas hashadas (bcrypt)
- [ ] Tokens CSRF en formularios
- [ ] Policies en todos los recursos
- [ ] Middleware en rutas sensibles
- [ ] Rate limiting en login
- [ ] Logs de acciones críticas
- [ ] Validación en múltiples capas
- [ ] Tests de autorización

---

## 📚 COMANDOS ÚTILES

```bash
# Crear migration
php artisan make:migration add_role_fields_to_users_table

# Crear policy
php artisan make:policy CitaPolicy --model=Cita

# Crear middleware
php artisan make:middleware AdminMiddleware

# Ejecutar migraciones
php artisan migrate

# Seed de datos
php artisan db:seed --class=RolesAndPermissionsSeeder

# Ver rutas
php artisan route:list

# Tinker (shell interactivo)
php artisan tinker
```

---

## 📞 SOPORTE

Si tienes dudas sobre:
- Policies: Ver `app/Policies/`
- Gates: Ver `app/Providers/AuthServiceProvider.php`
- Middleware: Ver `app/Http/Middleware/`
- Ejemplos: Ver `RUTAS_PROTEGIDAS.md` y `EJEMPLOS_VISTAS.blade`

