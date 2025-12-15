<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffMiddleware
{
    /**
     * Middleware para verificar que el usuario es empleado (staff).
     * 
     * Nota: No valida la categoría, solo que sea staff.
     * Para categorías específicas, usar StaffTypeMiddleware.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        if (!auth()->user()->isStaff() && !auth()->user()->isAdmin()) {
            abort(403, 'Se requiere ser empleado para acceder a esta sección.');
        }

        return $next($request);
    }
}
