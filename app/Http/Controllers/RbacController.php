<?php

namespace App\Http\Controllers;

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

        // No permitir cambiar el propio rol
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes cambiar tu propio rol.');
        }

        $usuario->rol = $request->rol;
        $usuario->save();

        return back()->with('success', "Rol de {$usuario->name} actualizado a {$usuario->rol}.");
    }
}
