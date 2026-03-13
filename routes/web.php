<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FavoritoController;
use App\Http\Controllers\OpenLibraryController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\LibroController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\UsuarioController;

Route::get('/open-library', function () {
    return view('libros.buscar', [
        'resultados' => null,
        'termino' => '',
        'categorias' => \App\Models\Categoria::all(),
    ]);
})->name('open-library.index');

Route::get('/open-library/buscar', [OpenLibraryController::class, 'buscar'])
    ->name('open-library.buscar');

Route::post('/open-library/importar', [OpenLibraryController::class, 'importar'])
    ->name('open-library.importar');

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/favoritos', [FavoritoController::class, 'index'])->name('favoritos');
    Route::post('/favoritos/{libro}/toggle', [FavoritoController::class, 'toggle'])->name('favoritos.toggle');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('password.request');

Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo');

Route::get('/logs', function () {
    return view('logs.index');
})->name('logs');

Route::post('/forgot-password', function () {
    return back()->with('status', 'Te enviamos el enlace a tu correo.');
})->name('password.email');

// Rutas solo para admin
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', function () {
        return 'Panel de administrador';
    })->name('admin.panel');
});

// Rutas solo para usuarios autenticados
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return 'Dashboard de usuario';
    })->name('dashboard');
});

Route::get('/libros/{libro}', [LibroController::class, 'show'])->name('libros.show');

Route::get('/buscar', function () {
    return view('libros.buscador');
})->name('buscar');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/robots.txt', function () {
    $content = "User-agent: *\n";
    $content .= "Disallow: /admin\n";
    $content .= "Disallow: /dashboard\n";
    $content .= "Disallow: /logs\n\n";
    $content .= "Sitemap: " . url('/sitemap.xml') . "\n";

    return response($content)->header('Content-Type', 'text/plain');
});

Route::middleware('auth')->group(function () {
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/{id}/edit', [UsuarioController::class, 'edit'])->name('usuarios.edit');
    Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
});
