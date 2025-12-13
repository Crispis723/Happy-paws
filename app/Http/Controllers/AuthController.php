<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request){
        // Validar los datos del formulario
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        // Verificar si el usuario existe y está activo
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {            
            $user = Auth::user();
            if ($user->activo) {
                return redirect()->route('dashboard'); // Redirigir al dashboard si es correcto
            } else {
                Auth::logout();
                return back()->with('error', 'Su cuenta está inactiva. Contacte al administrador.');
            }
        }
        return back()->with('error', 'Las credenciales no son correctas.');
    }
}
