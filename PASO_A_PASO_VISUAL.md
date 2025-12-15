# 🚀 PASO A PASO - IMPLEMENTACIÓN VISUAL

## INSTALACIÓN RÁPIDA (5 minutos)

### 1️⃣ Ejecutar Migración

```bash
cd C:\Proyecto\sistema
php artisan migrate
```

**¿Qué hace?**
- Agrega columnas `user_type`, `staff_type`, `activo`, `telefono` a tabla `users`
- Crea índices para optimización

**Resultado esperado:**
```
✓ Migration completed
```

---

### 2️⃣ Ejecutar Seeder

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

**¿Qué hace?**
- Crea 7 roles (admin, staff, public, contador, vet, recepcionista, gerente)
- Crea 6 usuarios de ejemplo:
  - 1 Admin
  - 1 Contador
  - 1 Veterinario
  - 1 Recepcionista
  - 1 Gerente
  - 1 Cliente público

**Usuarios para probar:**
```
┌─────────────────────┬─────────────────┬──────────────┐
│ Email               │ Contraseña      │ Tipo         │
├─────────────────────┼─────────────────┼──────────────┤
│ admin@clinica.test  │ password123     │ Admin        │
│ contador@clinica... │ password123     │ Contador     │
│ vet@clinica.test    │ password123     │ Veterinario  │
│ recepcion@...test   │ password123     │ Recepcionista│
│ gerente@clinica...  │ password123     │ Gerente      │
│ cliente@example...  │ password123     │ Cliente      │
└─────────────────────┴─────────────────┴──────────────┘
```

---

### 3️⃣ Probar Login

```bash
# Iniciar servidor
php artisan serve

# Acceder a http://127.0.0.1:8000/login
```

**Escenario 1: Login como Admin**
```
📧 admin@clinica.test
🔑 password123
↓
✅ Dashboard Admin
   ├─ Gestionar Usuarios
   ├─ Gestionar Roles
   └─ Configuración Sistema
```

**Escenario 2: Login como Veterinario**
```
📧 vet@clinica.test
🔑 password123
↓
✅ Dashboard Veterinario
   ├─ Mis Citas (solo asignadas)
   └─ Historiales Médicos
```

**Escenario 3: Login como Cliente**
```
📧 cliente@example.test
🔑 password123
↓
✅ Dashboard Cliente
   ├─ Mis Mascotas
   └─ Mis Citas
```

---

## 🎓 EJEMPLOS DE CÓDIGO

### Ejemplo 1: Proteger una ruta

**Archivo:** `routes/web.php`

```php
// Solo admin accede
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/users', [UserController::class, 'index']);
});

// Solo contador/gerente
Route::middleware(['auth', 'staff_type:contador,gerente'])->group(function () {
    Route::resource('ventas', VentaController::class);
});
```

---

### Ejemplo 2: Autorizar en controlador

**Archivo:** `app/Http/Controllers/CitaController.php`

```php
<?php

class CitaController extends Controller {
    
    // Opción 1: Autorizar todo automáticamente
    public function __construct() {
        $this->authorizeResource(Cita::class, 'cita');
    }
    
    // Opción 2: Autorizar manualmente en método
    public function destroy(Cita $cita) {
        $this->authorize('delete', $cita);  // ← Policy valida
        
        $cita->delete();
        return redirect()->back();
    }
}
```

---

### Ejemplo 3: Menú dinámico en vista

**Archivo:** `resources/views/sidebar.blade.php`

```blade
<nav>
    {{-- Todos ven --}}
    <a href="{{ route('dashboard') }}">Dashboard</a>
    
    {{-- Solo Admin --}}
    @if(auth()->user()->isAdmin())
        <a href="{{ route('usuarios.index') }}">Usuarios</a>
        <a href="{{ route('roles.index') }}">Roles</a>
    @endif
    
    {{-- Solo puede facturación --}}
    @if(auth()->user()->canAccessBilling())
        <a href="{{ route('ventas.index') }}">Ventas</a>
    @endif
    
    {{-- Solo veterinario --}}
    @if(auth()->user()->isStaffType('veterinario'))
        <a href="{{ route('historiales.index') }}">Historiales</a>
    @endif
    
    {{-- Solo clientes --}}
    @if(auth()->user()->isPublic())
        <a href="{{ route('mascotas.index') }}">Mis Mascotas</a>
    @endif
</nav>
```

---

### Ejemplo 4: Tabla con acciones dinámicas

**Archivo:** `resources/views/mascotas/index.blade.php`

```blade
<table>
    <tbody>
    @foreach($mascotas as $mascota)
        <tr>
            <td>{{ $mascota->nombre }}</td>
            <td>
                {{-- VER: Todos autorizados --}}
                @can('view', $mascota)
                    <a href="{{ route('mascotas.show', $mascota) }}">
                        👁️ Ver
                    </a>
                @endcan
                
                {{-- EDITAR: Solo propietario y staff --}}
                @can('update', $mascota)
                    <a href="{{ route('mascotas.edit', $mascota) }}">
                        ✏️ Editar
                    </a>
                @endcan
                
                {{-- ELIMINAR: Solo propietario y admin --}}
                @can('delete', $mascota)
                    <form action="{{ route('mascotas.destroy', $mascota) }}" 
                          method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button onclick="return confirm('¿Eliminar?')">
                            🗑️ Eliminar
                        </button>
                    </form>
                @endcan
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
```

---

## 🧪 PRUEBAS MANUALES

### Test 1: Admin puede ver todo

```
1. Login: admin@clinica.test / password123
2. Acceder a:
   ✓ /usuarios (lista de usuarios)
   ✓ /roles (gestión de roles)
   ✓ /admin/settings (configuración)
   ✓ /ventas (facturas)
   ✓ /mascotas (mascotas)
   ✓ /citas (citas)
```

### Test 2: Contador NO ve mascotas

```
1. Login: contador@clinica.test / password123
2. Intentar acceder a /mascotas
   ✗ 403 Forbidden (Policy lo bloquea)
3. Pero CAN acceder a:
   ✓ /ventas (facturación)
   ✓ /reportes/financieros
```

### Test 3: Veterinario solo ve citas asignadas

```
1. Login: vet@clinica.test / password123
2. Acceder a /citas/mis-citas
   ✓ Ver solo citas con veterinario_id = auth()->id()
3. Intentar acceder a /ventas
   ✗ 403 Forbidden
```

### Test 4: Cliente ve solo sus mascotas

```
1. Login: cliente@example.test / password123
2. Acceder a /mascotas
   ✓ Ver solo sus propias mascotas
3. Editar mascota propia
   ✓ Permitido
4. Eliminar mascota ajena
   ✗ 403 Forbidden
```

---

## 🔍 VERIFICAR LA IMPLEMENTACIÓN

### Verificar migración

```bash
# Ver columnas de users
php artisan tinker
DB::select('DESCRIBE users');

# Salida esperada:
# +---------+---+--------+...
# | Field   | Type | Null  |...
# +---------+---+--------+...
# | user_type | enum | YES  |...
# | staff_type | enum | YES  |...
# | activo  | tinyint | NO  |...
# +---------+---+--------+...
```

### Verificar usuarios creados

```bash
php artisan tinker

User::all()->map(fn($u) => [
    'name' => $u->name,
    'user_type' => $u->user_type,
    'staff_type' => $u->staff_type
])
```

**Salida esperada:**
```
[
  ['name' => 'Administrador', 'user_type' => 'admin', 'staff_type' => null],
  ['name' => 'Carlos Contador', 'user_type' => 'staff', 'staff_type' => 'contador'],
  ['name' => 'Dr. Veterinario', 'user_type' => 'staff', 'staff_type' => 'veterinario'],
  ...
]
```

### Verificar métodos en User

```bash
php artisan tinker

$user = User::first();
$user->isAdmin();          # true/false
$user->isStaffType('vet'); # true/false
$user->canAccessBilling(); # true/false
```

---

## 🚨 ERRORES COMUNES Y SOLUCIONES

### Error 1: "Class AuthServiceProvider not found"

**Causa:** AuthServiceProvider no está registrado  
**Solución:**
```bash
# Asegurar que existe en app/Providers/
# Y está registrado en config/app.php en 'providers'
```

### Error 2: "Target class [AdminMiddleware] not found"

**Causa:** Middleware no registrado en bootstrap/app.php  
**Solución:**
```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => AdminMiddleware::class,  // ← Agregar
    ]);
})
```

### Error 3: "User type not found" en migración

**Causa:** Campo ya existe  
**Solución:**
```bash
# Revisar tabla
php artisan tinker
Schema::hasColumn('users', 'user_type')  # true → ya existe
# Comentar esa línea de migración y re-ejecutar
```

### Error 4: Login redirecciona a /login en lugar de dashboard

**Causa:** Middleware 'guest' está bloqueando  
**Solución:**
```php
// routes/web.php
// Asegurar que /login está en 'guest' group
Route::middleware('guest')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});
```

---

## 🎬 WORKFLOW TÍPICO DE DESARROLLO

### Para agregar nuevo módulo protegido (Ej: Reportes)

**1. Crear controlador**
```bash
php artisan make:controller ReporteController
```

**2. Crear policy**
```bash
php artisan make:policy ReportePolicy --model=Reporte
```

**3. Registrar en AuthServiceProvider**
```php
protected $policies = [
    Reporte::class => ReportePolicy::class,
];
```

**4. Proteger rutas**
```php
// Solo contador/gerente
Route::middleware(['auth', 'staff_type:contador,gerente'])->group(function () {
    Route::resource('reportes', ReporteController::class);
});
```

**5. Usar en controlador**
```php
public function __construct() {
    $this->authorizeResource(Reporte::class, 'reporte');
}
```

**6. Usar en vista**
```blade
@can('create', App\Models\Reporte::class)
    <a href="{{ route('reportes.create') }}">Nuevo Reporte</a>
@endcan
```

---

## 📈 ESCALA DE COMPLEJIDAD

```
Nivel 1: Middleware simple (admin/staff/public)
├─ Fácil: Route::middleware('admin')
└─ Tiempo: 5 min

Nivel 2: Policies básicas (view/create/delete)
├─ Medio: Implementar en controlador
└─ Tiempo: 15 min

Nivel 3: Gates complejos (reglas personalizadas)
├─ Difícil: Lógica condicional
└─ Tiempo: 20 min

Nivel 4: Auditoría y logging
├─ Muy difícil: Event listeners
└─ Tiempo: 30 min
```

---

## 💾 ESTRUCTURA DE ARCHIVOS FINAL

```
proyecto/
├── app/
│   ├── Models/
│   │   └── User.php (actualizado con métodos helper)
│   ├── Policies/
│   │   ├── ClientePolicy.php
│   │   ├── MascotaPolicy.php
│   │   ├── CitaPolicy.php
│   │   └── VentaPolicy.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php (actualizado)
│   │   │   └── MascotaExampleController.php
│   │   └── Middleware/
│   │       ├── AdminMiddleware.php
│   │       ├── StaffMiddleware.php
│   │       ├── StaffTypeMiddleware.php
│   │       └── PublicMiddleware.php
│   └── Providers/
│       └── AuthServiceProvider.php (creado)
├── database/
│   ├── migrations/
│   │   └── 2025_12_15_000001_add_role_fields_to_users_table.php
│   └── seeders/
│       └── RolesAndPermissionsSeeder.php (actualizado)
├── bootstrap/
│   └── app.php (actualizado)
└── routes/
    └── web.php (actualizar con ejemplos)
```

---

## ✨ RESUMEN VISUAL

```
FLUJO DE AUTORIZACIÓN
═════════════════════

Usuario intenta acceder a /mascotas
       ↓
¿Está autenticado?
  ├─ NO → Redirige a /login
  └─ SÍ
       ↓
Middleware StaffTypeMiddleware evalúa
  ├─ ¿Permitido por tipo? (staff_type)
  │  ├─ NO → 403 Forbidden
  │  └─ SÍ
  │       ↓
  │   CitaController::index()
  │       ↓
  │   MascotaPolicy::viewAny()
  │   ├─ ¿Autorizado?
  │   │  ├─ NO → AuthorizationException
  │   │  └─ SÍ
  │   │       ↓
  │   │   Retorna vista con @can() checks
  │   │       ↓
  │   │   Botones mostrados según permisos
  │   │       ↓
  │   │   ✅ ACCESO CONCEDIDO
```

---

## 🎯 PRÓXIMOS PASOS

```
AHORA (HECHO)
├─ Migración con nuevos campos ✓
├─ Policies para módulos ✓
├─ Middleware por tipo ✓
├─ AuthServiceProvider ✓
└─ Seeders con usuarios ✓

LUEGO (OPCIONAL)
├─ Tests automatizados
├─ 2FA (Two-Factor Auth)
├─ API con tokens
├─ Auditoría avanzada
└─ Roles dinámicos creables por admin
```

---

¡**LISTO PARA PRODUCCIÓN!** 🚀

