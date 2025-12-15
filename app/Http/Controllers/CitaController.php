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
        return view('citas.create');
    }

    // Guardar la cita en BD
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha_hora' => 'required|date_format:Y-m-d H:i',
            'cliente_nombre' => 'required|string|max:255',
            'cliente_telefono' => 'required|string|max:20',
            'mascota_nombre' => 'required|string|max:255',
            'mascota_especie' => 'required|string|max:255',
            'motivo' => 'required|string',
            'precio' => 'nullable|numeric',
        ]);

        Cita::create($validated);
        return redirect()->route('citas.index')->with('success', 'Cita creada exitosamente.');
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
