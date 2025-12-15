<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

    public function register(Request $request)
    {
        // Validar los datos
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.unique' => 'Este correo ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener mínimo 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        // Crear el usuario
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'activo' => 1, // Activar automáticamente
        ]);

        // Autenticar al usuario
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', '¡Bienvenido! Tu cuenta ha sido creada.');
    }
}

