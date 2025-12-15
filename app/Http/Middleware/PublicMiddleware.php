<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PublicMiddleware
{
    /**
     * Middleware para usuarios públicos.
     * Redirige a usuarios autenticados al dashboard.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
