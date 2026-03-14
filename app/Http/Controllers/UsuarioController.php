<?php

namespace App\Http\Controllers;

use App\Repositories\UsuarioRepository;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function __construct(
        protected UsuarioRepository $usuarios
    ) {}

    public function index()
    {
        $usuarios = $this->usuarios->todos();
        return view('usuarios.index', compact('usuarios'));
    }

    public function edit($id)
    {
        $usuario = $this->usuarios->buscarPorId($id);
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
        ]);

        $this->usuarios->actualizar($id, $request->only('name', 'email'));

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy($id)
    {
        $this->usuarios->eliminar($id);
        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado correctamente');
    }
}