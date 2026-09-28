<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class RbacController extends Controller
{
    public function index()
    {
        $usuarios = User::orderBy('rol')->orderBy('name')->get();

        return view('admin.rbac.index', compact('usuarios'));
    }

    public function actualizar(Request $request, int $id)
    {
        $request->validate([
            'rol' => ['required', 'in:usuario,admin,superadmin'],
        ]);

        $usuario = User::findOrFail($id);
        $actor   = $request->user();

        // No permitir cambiar el propio rol
        if ($usuario->id === $actor->id) {
            return back()->with('error', 'No puedes cambiar tu propio rol.');
        }

        // Solo un superadmin puede otorgar o retirar el rol superadmin (issue #117)
        if (! $actor->puedeAsignarRol($request->rol, $usuario)) {
            return back()->with('error', 'Solo un superadministrador puede asignar o retirar el rol superadmin.');
        }

        $rolAnterior = $usuario->rol;
        $usuario->cambiarRol($request->rol);

        ActivityLog::registrar(
            'rbac_cambio_rol',
            "El usuario {$actor->name} cambió el rol de {$usuario->name} de {$rolAnterior} a {$usuario->rol} (panel de roles)."
        );

        return back()->with('success', "Rol de {$usuario->name} actualizado a {$usuario->rol}.");
    }
}
