<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    // Mostrar lista de citas
    public function index()
    {
        $citas = Cita::latest()->paginate(10);
        return view('citas.index', compact('citas'));
    }

    // Mostrar formulario para crear
    public function create()
    {
        // Si el usuario está autenticado y no tiene mascotas, forzamos crear una mascota primero.
        if (auth()->check() && auth()->user()->mascotas()->count() === 0) {
            return redirect()->route('mascotas.create')->with('info', 'Debes crear una mascota antes de pedir una cita.');
        }

        $mascotas = auth()->check() ? auth()->user()->mascotas()->get() : collect();
        $selected = request()->query('mascota_id');

        // Veterinarios disponibles (usuarios con rol 'veterinario')
        $veterinarios = \App\Models\User::role('veterinario')->get();

        return view('citas.create', ['mascotas' => $mascotas, 'selected_mascota_id' => $selected, 'veterinarios' => $veterinarios]);
    }

    // Guardar la cita en BD

    public function store(Request $request)
    {
        if ($request->has('fecha_hora')) {
            $request->merge(['fecha_hora' => str_replace('T', ' ', $request->fecha_hora)]);
        }

        // Si el usuario ha seleccionado una mascota existente
        if (auth()->check() && !$request->filled('mascota_id')) {
            // Usuario autenticado sin mascota seleccionada -> forzar crear/seleccionar mascota
            return redirect()->route('mascotas.create')->with('error', 'Debes seleccionar o crear una mascota antes de pedir una cita.');
        }

        if ($request->filled('mascota_id') && auth()->check()) {
            $request->validate([
                'mascota_id' => 'required|integer|exists:mascotas,id',
                'veterinario_id' => 'required|integer|exists:users,id',
                'fecha_hora' => 'required|date_format:Y-m-d H:i',
                'motivo' => 'required|string',
            ]);

            // Verificar propiedad
            $mascota = auth()->user()->mascotas()->find($request->mascota_id);
            if (!$mascota) {
                abort(403, 'Mascota inválida.');
            }

            // Verificar que el veterinario tiene el rol correcto
            $veterinario = \App\Models\User::find($request->veterinario_id);
            if (! $veterinario || ! $veterinario->hasRole('veterinario')) {
                abort(403, 'Veterinario inválido.');
            }

            $precio = \App\Models\Setting::get('cita_precio', '0.00');

            Cita::create([
                'fecha_hora' => $request->fecha_hora,
                'cliente_nombre' => auth()->user()->name,
                'cliente_telefono' => auth()->user()->telefono ?? $request->cliente_telefono ?? '',
                'mascota_id' => $mascota->id,
                'mascota_nombre' => $mascota->nombre, // opcional: guardar snapshot
                'mascota_especie' => $mascota->especie,
                'veterinario_id' => $veterinario->id,
                'motivo' => $request->motivo,
                'precio' => $precio,
            ]);

        } else {
            // Comportamiento anterior: datos manuales para invitado u "otra mascota"
            // Si el usuario autenticado selecciona una mascota existente
            if ($request->filled('mascota_id') && auth()->check()) {
                $request->validate([
                    'mascota_id' => 'required|integer|exists:mascotas,id',
                    'fecha_hora' => 'required|date_format:Y-m-d H:i',
                    'motivo' => 'required|string',
                    'precio' => 'nullable|numeric',
                ]);

                // Verificar que la mascota pertenece al usuario
                $mascota = auth()->user()->mascotas()->find($request->mascota_id);
                if (!$mascota) {
                    abort(403, 'Mascota inválida.');
                }

                Cita::create([
                    'fecha_hora' => $request->fecha_hora,
                    'cliente_nombre' => auth()->user()->name,
                    'cliente_telefono' => auth()->user()->telefono ?? $request->cliente_telefono ?? '',
                    'mascota_id' => $mascota->id,
                    'mascota_nombre' => $mascota->nombre,
                    'mascota_especie' => $mascota->especie,
                    'motivo' => $request->motivo,
                    'precio' => $request->precio,
                ]);
            } else {
                if (auth()->check()) {
                $validated = $request->validate([
                    'fecha_hora' => 'required|date_format:Y-m-d H:i',
                    'veterinario_id' => 'required|integer|exists:users,id',
                    'mascota_nombre' => 'required|string|max:255',
                    'mascota_especie' => 'required|string|max:255',
                    'motivo' => 'required|string',
                ]);

                // Verificar veterinario
                $veterinario = \App\Models\User::find($validated['veterinario_id']);
                if (! $veterinario || ! $veterinario->hasRole('veterinario')) {
                    abort(403, 'Veterinario inválido.');
                }

                $validated['cliente_nombre'] = auth()->user()->name;
                $validated['cliente_telefono'] = auth()->user()->telefono ?? '';
                $validated['veterinario_id'] = $veterinario->id;
                $validated['precio'] = \App\Models\Setting::get('cita_precio', '0.00');

                Cita::create($validated);
            } else {
                $validated = $request->validate([
                    'fecha_hora' => 'required|date_format:Y-m-d H:i',
                    'veterinario_id' => 'required|integer|exists:users,id',
                    'cliente_nombre' => 'required|string|max:255',
                    'cliente_telefono' => 'required|string|max:20',
                    'mascota_nombre' => 'required|string|max:255',
                    'mascota_especie' => 'required|string|max:255',
                    'motivo' => 'required|string',
                ]);

                $veterinario = \App\Models\User::find($validated['veterinario_id']);
                if (! $veterinario || ! $veterinario->hasRole('veterinario')) {
                    abort(403, 'Veterinario inválido.');
                }

                $validated['veterinario_id'] = $veterinario->id;
                $validated['precio'] = \App\Models\Setting::get('cita_precio', '0.00');

                Cita::create($validated);
            }
            }
        }

        // redirecciones y mensajes según tipo de usuario
        if (auth()->check()) {
            return redirect()->route('citas.index')->with('success', 'Cita creada exitosamente.');
        }

        return redirect('/')->with('success', 'Cita creada exitosamente.');
    }

    // Mostrar detalles de una cita
    public function show(Cita $cita)
    {
        return view('citas.show', compact('cita'));
    }

    // Mostrar formulario para editar
    public function edit(Cita $cita)
    {
        return view('citas.edit', compact('cita'));
    }

    // Actualizar en BD
    public function update(Request $request, Cita $cita)
    {
        // Normalize datetime-local input
        if ($request->has('fecha_hora')) {
            $request->merge(['fecha_hora' => str_replace('T', ' ', $request->fecha_hora)]);
        }

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
        return redirect()->route('citas.index')->with('success', 'Cita actualizada exitosamente.');
    }

    // Eliminar
    public function destroy(Cita $cita)
    {
        $cita->delete();
        return redirect()->route('citas.index')->with('success', 'Cita eliminada exitosamente.');
    }
}
