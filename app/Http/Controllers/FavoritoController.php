<?php

namespace App\Http\Controllers;

use App\Models\Favorito;
use App\Models\Libro;
use Illuminate\Http\Request;

class FavoritoController extends Controller
{
    // Mostrar lista de favoritos del usuario
    public function index()
    {
        $favoritos = auth()->user()->favoritos()->with('libro')->get();
        return view('favoritos.index', compact('favoritos'));
    }

    // Agregar o quitar favorito (toggle)
    public function toggle(Libro $libro)
    {
        $usuario = auth()->user();

        if ($usuario->esFavorito($libro->id)) {
            $usuario->favoritos()->where('libro_id', $libro->id)->delete();
            $esFavorito = false;
        } else {
            Favorito::create([
                'user_id' => $usuario->id,
                'libro_id' => $libro->id,
            ]);
            $esFavorito = true;
        }

        return response()->json([
            'esFavorito' => $esFavorito,
        ]);
    }
}