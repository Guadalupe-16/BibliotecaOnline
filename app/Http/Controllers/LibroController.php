<?php

namespace App\Http\Controllers;

use App\Models\Libro;

class LibroController extends Controller
{
    public function show(Libro $libro)
    {
        $libro->load(['autor', 'categoria']);

        return view('libros.detalle', compact('libro'));
    }
}