<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\VerificacionPinMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function registrar(Request $request)
    {
        $request->validate([
            'nombre'               => ['required', 'string', 'max:255'],
            'apellidos'            => ['required', 'string', 'max:255'],
            'email'                => ['required', 'email', 'unique:users,email'],
            'username'             => ['required', 'string', 'max:255', 'unique:users,name'],
            'password'             => ['required', 'min:8', 'confirmed'],
            'terminos'             => ['accepted'],
        ]);

        $usuario = User::crear([
            'name'     => $request->nombre . ' ' . $request->apellidos,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'rol'      => 'usuario',
        ]);

        $this->enviarPin($usuario);

        return redirect()->route('verificar.email.mostrar', $usuario->id)
            ->with('info', 'Te enviamos un PIN de verificación a tu correo.');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $usuario = User::where('email', $request->email)->first();

        if (! $usuario || ! Hash::check($request->password, $usuario->password)) {
            return back()->withInput()->with('error', 'Credenciales incorrectas.');
        }

        if (! $usuario->estaVerificado()) {
            $this->enviarPin($usuario);
            return redirect()->route('verificar.email.mostrar', $usuario->id)
                ->with('info', 'Debes verificar tu correo antes de ingresar. Te reenviamos el PIN.');
        }

        Auth::login($usuario, $request->boolean('remember'));

        return redirect()->intended(route('catalogo'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function enviarPin(User $usuario): void
    {
        $pin = random_int(100000, 999999);
        Cache::put("email_pin_{$usuario->id}", $pin, now()->addMinutes(15));
        Mail::to($usuario->email)->send(new VerificacionPinMail($pin));
    }
}
