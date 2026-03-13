<?php

namespace App\Http\Controllers;

use App\Models\Libro;

class CatalogoController extends Controller
{
    public function index()
    {
        $libros = Libro::with(['autor', 'categoria'])
            ->orderBy('titulo')
            ->paginate(12);

        return view('libros.catalogo', compact('libros'));
    }
}