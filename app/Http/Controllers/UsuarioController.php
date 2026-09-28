<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
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

        $usuario = $this->usuarios->actualizar($id, $request->only('name', 'email'));

        ActivityLog::registrar(
            'usuario_actualizado',
            'El usuario ' . auth()->user()->name . " actualizó los datos de {$usuario->name} (#{$usuario->id})."
        );

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy($id)
    {
        $nombre = $this->usuarios->buscarPorId($id)->name;
        $this->usuarios->eliminar($id);

        ActivityLog::registrar(
            'usuario_eliminado',
            'El usuario ' . auth()->user()->name . " eliminó al usuario {$nombre} (#{$id})."
        );
        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado correctamente');
    }
}