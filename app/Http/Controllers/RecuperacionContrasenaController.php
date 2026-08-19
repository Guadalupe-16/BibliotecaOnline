<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RecuperacionContrasenaController extends Controller
{
    public function enviarEnlace(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $estado = Password::sendResetLink(
            $request->only('email')
        );

        return $estado === Password::RESET_LINK_SENT
            ? back()->with('status', __($estado))
            : back()->withInput()->withErrors(['email' => __($estado)]);
    }

    public function mostrarFormularioReset(string $token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetear(Request $request)
    {
        $request->validate([
            'token'                 => ['required'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'min:8', 'confirmed'],
        ]);

        $estado = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($usuario, $contrasena) {
                $usuario->forceFill([
                    'password'       => Hash::make($contrasena),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($usuario));
            }
        );

        if ($estado === Password::PASSWORD_RESET) {
            // El usuario no queda autenticado, asi que se le manda a iniciar
            // sesion: la vista de login ya muestra este mensaje de exito.
            return redirect()->route('login')->with('status', 'Tu contraseña ha sido restablecida correctamente.');
        }

        return back()->withInput()->withErrors(['email' => __($estado)]);
    }
}
