<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * EJEMPLO DE CONTROLADOR CON PROTECCIONES COMPLETAS
 * 
 * Este archivo muestra cómo implementar autorización en un controlador
 * usando Policies, Gates y middleware.
 * 
 * Estructura:
 * 1. Autorización en constructor (aplica a todos los métodos)
 * 2. Validación adicional de permisos en métodos específicos
 * 3. Uso de Gates para lógica personalizada
 */
class MascotaExampleController extends Controller
{
    /**
     * OPCIÓN 1: Autorizar todo automáticamente con authorizeResource
     * 
     * Esto mapea automáticamente:
     * - index/create/store → no requiere autorización (viewAny)
     * - show → view
     * - edit/update → update
     * - destroy → delete
     */
    public function __construct()
    {
        // Autorizar todas las acciones contra MascotaPolicy
        $this->authorizeResource(Mascota::class, 'mascota');
    }

    /**
     * Listar mascotas.
     * 
     * Autorización: Via authorizeResource → MascotaPolicy@viewAny
     * 
     * Quién puede acceder:
     * - Admin
     * - Recepcionista
     * - Gerente
     * - Public (sus propias mascotas)
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin() || $user->isStaffType('recepcionista') || $user->isStaffType('gerente')) {
            // Staff ve todas las mascotas
            $mascotas = Mascota::paginate(10);
        } else {
            // Public solo sus mascotas
            $mascotas = auth()->user()->mascotas()->paginate(10);
        }

        return view('mascotas.index', compact('mascotas'));
    }

    /**
     * Ver detalle de mascota.
     * 
     * Autorización: Via authorizeResource → MascotaPolicy@view
     */
    public function show(Mascota $mascota)
    {
        // La autorización se hace automáticamente en el constructor
        // Si no tiene permiso, lanza AuthorizationException
        return view('mascotas.show', compact('mascota'));
    }

    /**
     * Formulario para crear mascota.
     * 
     * Autorización: Via authorizeResource → MascotaPolicy@create
     */
    public function create()
    {
        return view('mascotas.create');
    }

    /**
     * Guardar mascota.
     * 
     * Autorización: Via authorizeResource → MascotaPolicy@create
     * 
     * SEGURIDAD CRÍTICA:
     * - Users públicos solo crean sus propias mascotas
     * - Staff puede crear para cualquiera
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'especie' => 'required|string',
            'raza' => 'nullable|string',
            'edad' => 'nullable|integer',
        ]);

        // Si es public, asociar a sí mismo
        if (auth()->user()->isPublic()) {
            $validated['user_id'] = auth()->id();
        } else if ($request->filled('user_id')) {
            // Si es staff, permitir crear para otro usuario
            // Pero con validación adicional
            $this->authorize('manage-citas'); // Gate personalizado
            $validated['user_id'] = $request->user_id;
        }

        $mascota = Mascota::create($validated);

        return redirect()->route('mascotas.show', $mascota)
            ->with('success', 'Mascota creada exitosamente');
    }

    /**
     * Formulario para editar.
     * 
     * Autorización: Via authorizeResource → MascotaPolicy@update
     */
    public function edit(Mascota $mascota)
    {
        return view('mascotas.edit', compact('mascota'));
    }

    /**
     * Actualizar mascota.
     * 
     * Autorización: Via authorizeResource → MascotaPolicy@update
     * 
     * Quién puede editar:
     * - El propietario (si es public)
     * - Staff (recepcionista, gerente, admin)
     */
    public function update(Request $request, Mascota $mascota)
    {
        // Autorización automática en constructor

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'especie' => 'required|string',
            'raza' => 'nullable|string',
            'edad' => 'nullable|integer',
        ]);

        $mascota->update($validated);

        return redirect()->route('mascotas.show', $mascota)
            ->with('success', 'Mascota actualizada exitosamente');
    }

    /**
     * Eliminar mascota.
     * 
     * Autorización: Via authorizeResource → MascotaPolicy@delete
     * 
     * Quién puede eliminar:
     * - El propietario (si es public)
     * - Admin
     * - Gerente
     */
    public function destroy(Mascota $mascota)
    {
        // Autorización automática en constructor
        $mascota->delete();

        return redirect()->route('mascotas.index')
            ->with('success', 'Mascota eliminada exitosamente');
    }

    /**
     * OPCIÓN 2: Autorización manual en métodos específicos
     * 
     * Si prefieres mayor control sin usar authorizeResource,
     * puedes hacerlo manual en cada método:
     */
    public function manualExample(Mascota $mascota)
    {
        // Opción A: Usar Policy
        $this->authorize('view', $mascota);

        // Opción B: Usar Gate
        \Illuminate\Support\Facades\Gate::authorize('access-medical');

        // Opción C: Validar directamente
        if (!auth()->user()->can('update', $mascota)) {
            abort(403, 'No tienes permiso para actualizar esta mascota');
        }

        return view('mascotas.show', compact('mascota'));
    }

    /**
     * OPCIÓN 3: Autorización en middleware de ruta
     * 
     * En web.php:
     *   Route::delete('mascotas/{mascota}', [MascotaController::class, 'destroy'])
     *       ->middleware('can:delete,mascota');
     */
}
