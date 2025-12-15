<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Middleware para verificar que el usuario es administrador.
     * 
     * Uso en rutas:
     *   Route::middleware('admin')->group(...)
     * 
     * O en controlador:
     *   $this->middleware('admin')->only(['destroy']);
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        if (!auth()->user()->isAdmin()) {
            abort(403, 'No tienes permiso para acceder a esta sección. Se requiere ser administrador.');
        }

        return $next($request);
    }
}
