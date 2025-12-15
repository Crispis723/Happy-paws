<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffTypeMiddleware
{
    /**
     * Middleware para verificar categoría de empleado específica.
     * 
     * Uso en rutas:
     *   Route::middleware('staff_type:contador,gerente')->group(...)
     * 
     * Permite múltiples tipos separados por coma.
     */
    public function handle(Request $request, Closure $next, string $staffTypes): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();
        $allowedTypes = array_map('trim', explode(',', $staffTypes));

        // Admin siempre tiene acceso
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Verificar si es staff y su tipo está permitido
        if ($user->isStaff() && in_array($user->staff_type, $allowedTypes)) {
            return $next($request);
        }

        abort(403, 'No tienes permiso para acceder a esta sección.');
    }
}
