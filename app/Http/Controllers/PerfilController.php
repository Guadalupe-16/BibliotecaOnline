<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PerfilController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();
        return view('perfil.index', compact('usuario'));
    }

    public function actualizar(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'foto'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $usuario = auth()->user();
        $usuario->name = $request->name;

        if ($request->hasFile('foto')) {
            // Eliminar foto anterior si existe
            if ($usuario->foto) {
                \Storage::disk('public')->delete($usuario->foto);
            }

            $ruta = $request->file('foto')->store('fotos', 'public');
            $usuario->foto = $ruta;
        }

        $usuario->save();

        return back()->with('success', 'Perfil actualizado correctamente.');
    }
}