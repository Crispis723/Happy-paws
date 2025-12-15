<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Dashboard principal.
     * 
     * Redirecciona según el tipo de usuario:
     * - admin y staff → dashboard unificado con módulos según permisos
     * - public → dashboard público simplificado
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Redirigir según tipo de usuario
        if ($user->isAdmin() || $user->isStaff()) {
            return view('dashboard.staff');
        }

        if ($user->isPublic()) {
            return view('dashboard.public');
        }

        // Fallback
        return redirect()->route('login');
    }

    /**
     * Dashboard para admin y staff (unificado).
     * 
     * Los módulos se muestran según permisos usando @can en la vista.
     */
    public function staff()
    {
        return view('dashboard.staff');
    }

    /**
     * Dashboard para usuarios públicos.
     */
    public function public()
    {
        return view('dashboard.public');
    }
}
