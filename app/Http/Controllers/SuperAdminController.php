<?php

namespace App\Http\Controllers;

use App\Models\User;

class SuperAdminController extends Controller
{
    public function index()
    {
        $usuarios = User::orderBy('name')->get();
        $total_usuarios = $usuarios->count();
        $total_admins = $usuarios->where('rol', 'admin')->count();
        $total_superadmins = $usuarios->where('rol', 'superadmin')->count();

        return view('superadmin.index', compact(
            'usuarios',
            'total_usuarios',
            'total_admins',
            'total_superadmins'
        ));
    }

    public function toggleEstado($id)
    {
        $usuario = User::findOrFail($id);

        // No puede desactivarse a sí mismo
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $usuario->activo = !$usuario->activo;
        $usuario->save();

        return back()->with('success', 'Estado del usuario actualizado.');
    }

    public function cambiarRol($id, $nuevoRol)
    {
        $rolesValidos = ['usuario', 'admin', 'superadmin'];

        if (!in_array($nuevoRol, $rolesValidos)) {
            return back()->with('error', 'Rol no válido.');
        }

        $usuario = User::findOrFail($id);
        $usuario->rol = $nuevoRol;
        $usuario->save();

        return back()->with('success', "Rol actualizado a {$nuevoRol}.");
    }
}