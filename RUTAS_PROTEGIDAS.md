Rut as Protegidas - Ejemplos de Implementación
==========================================

## ESTRUCTURA GENERAL

```
/ (landing) - público
/login - público
/register - público
/citas/create - público (invitado)
/citas/{id} - protegido por CitaPolicy

/admin/* - admin only
/staff/* - staff y admin
/public/* - public users
```

## EJEMPLOS DE RUTAS CON PROTECCIONES

### 1. RUTAS PÚBLICAS (sin autenticación)
```php
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return view('landing');
    })->name('landing');
    Route::get('login', function(){
        return view('autenticacion.login');
    })->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// Citas públicas (invitados pueden ver formulario)
Route::get('citas/create', [CitaController::class, 'create'])->name('citas.create');
Route::post('citas', [CitaController::class, 'store'])->name('citas.store');
```

### 2. RUTAS ADMIN ONLY
```php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('usuarios', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::get('admin/settings', [SettingController::class, 'index'])->name('admin.settings');
});
```

### 3. RUTAS STAFF (todos los empleados)
```php
Route::middleware(['auth', 'staff'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('clientes', ClienteController::class)->except(['destroy']);
});
```

### 4. RUTAS CON CATEGORÍA ESPECÍFICA (staff_type)
```php
// Solo contadores
Route::middleware(['auth', 'staff_type:contador,gerente'])->group(function () {
    Route::resource('ventas', VentaController::class);
    Route::resource('compras', CompraController::class);
    Route::get('reportes/financieros', [ReporteController::class, 'financial']);
});

// Solo veterinarios
Route::middleware(['auth', 'staff_type:veterinario,gerente'])->group(function () {
    Route::get('citas/mis-citas', [CitaController::class, 'myAppointments']);
    Route::get('historiales/{mascota}', [HistorialController::class, 'show']);
});

// Recepcionista y gerente
Route::middleware(['auth', 'staff_type:recepcionista,gerente'])->group(function () {
    Route::resource('citas', CitaController::class)->except(['create','store']);
});
```

### 5. RUTAS PÚBLICAS (usuarios registrados)
```php
Route::middleware(['auth', 'public'])->group(function () {
    Route::get('/public/dashboard', [PublicDashboardController::class, 'index'])->name('public.dashboard');
    Route::resource('mascotas', MascotaController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::get('mis-citas', [CitaController::class, 'myAppointments']);
});
```

## PROTECCIÓN CON POLICIES EN CONTROLADORES

### Autorizar acciones en el controlador

```php
// En el método del controlador
public function destroy(Cita $cita)
{
    $this->authorize('delete', $cita);  // Verifica CitaPolicy@delete
    $cita->delete();
    return redirect()->route('citas.index')->with('success', 'Eliminado');
}
```

### En el constructor (aplicar a múltiples métodos)

```php
public function __construct()
{
    $this->authorizeResource(Cita::class, 'cita');
}

// Esto autoriza automáticamente:
// - show → view
// - create → create
// - store → create
// - edit → update
// - update → update
// - destroy → delete
```

## PROTECCIÓN EN VISTAS

### Mostrar/ocultar elementos según permisos

```blade
{{-- Opción 1: Con Policy --}}
@can('create', App\Models\Cita::class)
    <a href="{{ route('citas.create') }}" class="btn btn-primary">Nueva Cita</a>
@endcan

{{-- Opción 2: Con Gate --}}
@can('access-billing')
    <a href="{{ route('ventas.index') }}">Ver Ventas</a>
@endcan

{{-- Opción 3: Con métodos del modelo User --}}
@if(auth()->user()->isStaffType('veterinario'))
    <a href="{{ route('historiales.index') }}">Mis Historiales</a>
@endif

{{-- Opción 4: Mostrar solo si es propietario --}}
@can('view', $mascota)
    <div>{{ $mascota->nombre }}</div>
@endcan
```

## EXCEPCIONES Y ERRORES

Si un usuario NO tiene permiso:
- Middleware: Retorna 403 con mensaje personalizado
- Policy: Lanza `AuthorizationException`
- Gate: Retorna false (se puede capturar)

Para capturar:

```php
public function edit(Mascota $mascota)
{
    Gate::authorize('update', $mascota);  // Lanza excepción si no autorizado
    
    return view('mascotas.edit', compact('mascota'));
}
```

O con try-catch:

```php
try {
    $this->authorize('delete', $cita);
} catch (AuthorizationException $e) {
    return redirect()->back()->with('error', 'No tienes permiso para realizar esta acción');
}
```

## RESUMEN DE PROTECCIONES

| Elemento | Cómo se protege | Dónde |
|----------|-----------------|-------|
| Rutas | Middleware | routes/web.php |
| Acciones | Policies/Gates | Controladores |
| Datos en BD | Queries con where | Modelos |
| Vistas | @can/@gate | Blade templates |

