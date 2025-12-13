<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\User;

class ReporteController extends Controller
{
    public function reporteVentas(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'user_id' => 'nullable|exists:users,id'
        ]);
        $query = Venta::with(['user', 'cliente', 'detalles.producto.afectacionTipo']);

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $ventas = $query->paginate(10)->appends($request->all()); 
        $users = User::select('id', 'name')->get();

        return view('reportes.ventas', compact('ventas', 'users'));
    }
}
