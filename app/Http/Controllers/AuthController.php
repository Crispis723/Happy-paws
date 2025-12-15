<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Procesar login.
     * 
     * Autenticación simple por email y contraseña.
     * El rol se obtiene de la BD, no es seleccionable por el usuario.
     * Redirección automática según el tipo de usuario.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Verificar que el usuario esté activo
            if (!$user->activo) {
                Auth::logout();
                return back()->with('error', 'Su cuenta está inactiva. Contacte al administrador.');
            }

            // Redirigir según tipo de usuario
            return $this->redirectByUserType($user);
        }

        return back()->with('error', 'Las credenciales no son correctas.');
    }

    /**
     * Procesar registro (solo usuarios públicos).
     * 
     * ⚠️ SEGURIDAD CRÍTICA:
     * - Los usuarios se crean SIEMPRE como 'public'
     * - NO se permiten subroles en registro
     * - Staff y admin se crean SOLO mediante admin panel
     */
    public function register(Request $request)
    {
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

        // Crear usuario como 'public' (SIEMPRE)
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'user_type' => 'public',      // 🔐 Siempre public
            'staff_type' => null,           // 🔐 Nunca empleado
            'activo' => 1,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', '¡Bienvenido! Tu cuenta ha sido creada.');
    }

    /**
     * Redirigir al usuario según su tipo.
     * 
     * LÓGICA:
     * - admin y staff -> /dashboard (dashboard unificado con módulos según permisos)
     * - public -> /dashboard (vista pública simple)
     * - El dashboard detecta automáticamente el tipo y carga la vista correcta
     */
    protected function redirectByUserType(User $user): \Illuminate\Http\RedirectResponse
    {
        // Un solo dashboard que se adapta según el usuario
        return redirect()->route('dashboard')->with(
            'success', 
            "¡Bienvenido {$user->name}!"
        );
    }
}
