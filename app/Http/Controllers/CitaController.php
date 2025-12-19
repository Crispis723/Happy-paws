<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\User;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    /**
     * Display list of appointments
     */
    public function index()
    {
        $citas = Cita::latest()->paginate(10);
        return view('citas.index', compact('citas'));
    }

    /**
     * Show form to create new appointment
     */
    public function create()
    {
        // Require authenticated user to have at least one mascota
        if (auth()->check() && auth()->user()->mascotas()->count() === 0) {
            return redirect()->route('mascotas.create')
                ->with('info', 'Debes crear una mascota antes de pedir una cita.');
        }

        $mascotas = auth()->check() ? auth()->user()->mascotas()->get() : collect();
        $veterinarios = User::role('veterinario')->get();

        return view('citas.create', [
            'mascotas' => $mascotas,
            'selected_mascota_id' => request()->query('mascota_id'),
            'veterinarios' => $veterinarios,
        ]);
    }

    /**
     * Store appointment in database
     */
    public function store(Request $request)
    {
        // Normalize datetime-local input (T separator to space)
        $this->normalizeDatetime($request);

        // Determine validation rules based on user state and form data
        $validated = $this->validateCitaRequest($request);

        // Get veterinarian and verify role
        $veterinario = User::findOrFail($validated['veterinario_id']);
        if (!$veterinario->hasRole('veterinario')) {
            abort(403, 'El usuario seleccionado no es un veterinario válido.');
        }

        // Handle mascota (existing or manual entry)
        if ($request->filled('mascota_id') && auth()->check()) {
            $mascota = auth()->user()->mascotas()->findOrFail($request->mascota_id);
            $citaData = $this->prepareCitaDataWithExistingMascota($request, $validated, $mascota, $veterinario);
        } else {
            $citaData = $this->prepareCitaDataWithManualEntry($request, $validated, $veterinario);
        }

        Cita::create($citaData);

        // Redirect based on user authentication
        $redirectUrl = auth()->check() ? route('citas.index') : '/';
        return redirect($redirectUrl)->with('success', 'Cita creada exitosamente.');
    }

    /**
     * Display appointment details
     */
    public function show(Cita $cita)
    {
        return view('citas.show', compact('cita'));
    }

    /**
     * Show form to edit appointment
     */
    public function edit(Cita $cita)
    {
        return view('citas.edit', compact('cita'));
    }

    /**
     * Update appointment in database
     */
    public function update(Request $request, Cita $cita)
    {
        $this->normalizeDatetime($request);

        $validated = $request->validate([
            'fecha_hora' => 'required|date_format:Y-m-d H:i',
            'cliente_nombre' => 'required|string|max:255',
            'cliente_telefono' => 'required|string|max:20',
            'mascota_nombre' => 'required|string|max:255',
            'mascota_especie' => 'required|string|max:255',
            'motivo' => 'required|string',
            'estado' => 'in:pendiente,confirmada,completada,cancelada',
            'precio' => 'nullable|numeric',
            'notas' => 'nullable|string',
        ]);

        $cita->update($validated);

        return redirect()->route('citas.index')
            ->with('success', 'Cita actualizada exitosamente.');
    }

    /**
     * Delete appointment
     */
    public function destroy(Cita $cita)
    {
        $cita->delete();

        return redirect()->route('citas.index')
            ->with('success', 'Cita eliminada exitosamente.');
    }

    /**
     * Normalize datetime-local input format
     */
    private function normalizeDatetime(Request $request): void
    {
        if ($request->has('fecha_hora')) {
            $request->merge(['fecha_hora' => str_replace('T', ' ', $request->fecha_hora)]);
        }
    }

    /**
     * Validate appointment request based on user state
     */
    private function validateCitaRequest(Request $request): array
    {
        $isAuthenticated = auth()->check();
        $hasMascotaId = $request->filled('mascota_id');

        // Base rules required for all cases
        $baseRules = [
            'fecha_hora' => 'required|date_format:Y-m-d H:i',
            'veterinario_id' => 'required|integer|exists:users,id',
            'motivo' => 'required|string',
        ];

        if ($isAuthenticated && $hasMascotaId) {
            // Authenticated user with existing mascota
            return $request->validate(array_merge($baseRules, [
                'mascota_id' => 'required|integer|exists:mascotas,id',
            ]));
        }

        if ($isAuthenticated) {
            // Authenticated user with manual mascota entry
            return $request->validate(array_merge($baseRules, [
                'mascota_nombre' => 'required|string|max:255',
                'mascota_especie' => 'required|string|max:255',
            ]));
        }

        // Guest user (all fields manual)
        return $request->validate(array_merge($baseRules, [
            'cliente_nombre' => 'required|string|max:255',
            'cliente_telefono' => 'required|string|max:20',
            'mascota_nombre' => 'required|string|max:255',
            'mascota_especie' => 'required|string|max:255',
        ]));
    }

    /**
     * Prepare cita data for existing mascota
     */
    private function prepareCitaDataWithExistingMascota(
        Request $request,
        array $validated,
        $mascota,
        $veterinario
    ): array {
        return [
            'fecha_hora' => $validated['fecha_hora'],
            'cliente_nombre' => auth()->user()->name,
            'cliente_telefono' => auth()->user()->telefono ?? '',
            'mascota_id' => $mascota->id,
            'mascota_nombre' => $mascota->nombre,
            'mascota_especie' => $mascota->especie,
            'veterinario_id' => $veterinario->id,
            'motivo' => $validated['motivo'],
            'precio' => \App\Models\Setting::get('cita_precio', '0.00'),
        ];
    }

    /**
     * Prepare cita data for manual entry
     */
    private function prepareCitaDataWithManualEntry(Request $request, array $validated, $veterinario): array
    {
        return [
            'fecha_hora' => $validated['fecha_hora'],
            'cliente_nombre' => auth()->check() ? auth()->user()->name : $validated['cliente_nombre'],
            'cliente_telefono' => auth()->check() ? (auth()->user()->telefono ?? '') : $validated['cliente_telefono'],
            'mascota_nombre' => $validated['mascota_nombre'],
            'mascota_especie' => $validated['mascota_especie'],
            'veterinario_id' => $veterinario->id,
            'motivo' => $validated['motivo'],
            'precio' => $request->filled('precio') ? $request->precio : \App\Models\Setting::get('cita_precio', '0.00'),
        ];
    }
}
