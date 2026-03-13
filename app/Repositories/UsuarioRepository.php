<?php

namespace App\Repositories;

use App\Models\User;

class UsuarioRepository
{
    public function todos()
    {
        return User::orderBy('name')->get();
    }

    public function buscarPorId($id)
    {
        return User::findOrFail($id);
    }

    public function actualizar($id, array $datos)
    {
        $usuario = User::findOrFail($id);
        $usuario->update($datos);
        return $usuario;
    }

    public function eliminar($id)
    {
        $usuario = User::findOrFail($id);
        $usuario->delete();
    }
}