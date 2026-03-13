<?php

namespace App\Http\Controllers;

use App\Models\Libro;

class SitemapController extends Controller
{
    public function index()
    {
        $libros = Libro::select('id', 'updated_at')->get();

        return response()
            ->view('sitemap', compact('libros'))
            ->header('Content-Type', 'application/xml');
    }
}
