<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use Illuminate\Http\Request;

class MascotaController extends Controller
{
    public function index()
    {
        $mascotas = auth()->user()->mascotas()->paginate(10);
        return view('mascotas.index', compact('mascotas'));
    }

    public function create()
    {
        return view('mascotas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'especie' => 'required|string|max:255',
            'raza' => 'nullable|string|max:255',
            'sexo' => 'nullable|in:m,h',
            'fecha_nacimiento' => 'nullable|date',
            'notas' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();

        Mascota::create($validated);

        return redirect()->route('mascotas.index')->with('success', 'Mascota creada.');
    }

    public function show(Mascota $mascota)
    {
        if ($mascota->user_id !== auth()->id()) { abort(403); }
        return view('mascotas.show', compact('mascota'));
    }

    public function edit(Mascota $mascota)
    {
        if ($mascota->user_id !== auth()->id()) { abort(403); }
        return view('mascotas.edit', compact('mascota'));
    }

    public function update(Request $request, Mascota $mascota)
    {
        if ($mascota->user_id !== auth()->id()) { abort(403); }
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'especie' => 'required|string|max:255',
            'raza' => 'nullable|string|max:255',
            'sexo' => 'nullable|in:m,h',
            'fecha_nacimiento' => 'nullable|date',
            'notas' => 'nullable|string',
        ]);
        $mascota->update($validated);
        return redirect()->route('mascotas.index')->with('success', 'Mascota actualizada.');
    }

    public function destroy(Mascota $mascota)
    {
        if ($mascota->user_id !== auth()->id()) { abort(403); }
        $mascota->delete();
        return redirect()->route('mascotas.index')->with('success', 'Mascota eliminada.');
    }
}
