<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class VerificacionEmailController extends Controller
{
    public function mostrar(int $id)
    {
        $usuario = User::findOrFail($id);

        if ($usuario->estaVerificado()) {
            return redirect()->route('login');
        }

        return view('auth.verify-pin', compact('usuario'));
    }

    public function verificar(Request $request, int $id)
    {
        $request->validate([
            'pin' => ['required', 'digits:6'],
        ]);

        $usuario = User::findOrFail($id);
        $pinGuardado = Cache::get("email_pin_{$usuario->id}");

        if (! $pinGuardado || (int) $request->pin !== (int) $pinGuardado) {
            return back()->withErrors(['pin' => 'El PIN es incorrecto o ha expirado.']);
        }

        $usuario->marcarComoVerificado();
        Cache::forget("email_pin_{$usuario->id}");

        Auth::login($usuario);

        return redirect()->route('catalogo')
            ->with('success', '¡Correo verificado! Bienvenido a la Biblioteca Digital.');
    }

    public function reenviar(int $id)
    {
        $usuario = User::findOrFail($id);

        if ($usuario->estaVerificado()) {
            return redirect()->route('login');
        }

        app(AuthController::class)->enviarPin($usuario);

        return back()->with('info', 'Se reenvió el PIN a tu correo.');
    }
}
