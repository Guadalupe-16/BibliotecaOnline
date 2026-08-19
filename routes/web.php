<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FavoritoController;
use App\Http\Controllers\OpenLibraryController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\LibroController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\RecuperacionContrasenaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerificacionEmailController;
use App\Http\Controllers\RbacController;

Route::get('/open-library', function () {
    return view('libros.buscar', [
        'resultados' => null,
        'termino' => '',
        'categorias' => \App\Models\Categoria::all(),
    ]);
})->name('open-library.index');

Route::middleware('auth')->group(function () {
    Route::get('/open-library/buscar', [OpenLibraryController::class, 'buscar'])
        ->name('open-library.buscar');

    Route::post('/open-library/importar', [OpenLibraryController::class, 'importar'])
        ->middleware('role:admin,superadmin')
        ->name('open-library.importar');
});

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/favoritos', [FavoritoController::class, 'index'])->name('favoritos');
    Route::post('/favoritos/{libro}/toggle', [FavoritoController::class, 'toggle'])->name('favoritos.toggle');
    Route::post('/logout', function () {
        auth()->logout();
        return redirect('/');
    })->name('logout');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', fn() => view('auth.login'))->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

    Route::get('/register', fn() => view('auth.register'))->name('register');
    Route::post('/register', [AuthController::class, 'registrar'])->middleware('throttle:registro');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/verificar-email/{id}', [VerificacionEmailController::class, 'mostrar'])->name('verificar.email.mostrar');
Route::post('/verificar-email/{id}', [VerificacionEmailController::class, 'verificar'])
    ->middleware('throttle:verificacion-pin')
    ->name('verificar.email.verificar');
Route::post('/verificar-email/{id}/reenviar', [VerificacionEmailController::class, 'reenviar'])
    ->middleware('throttle:reenvio-pin')
    ->name('verificar.email.reenviar');

Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('password.request');

Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo');


Route::post('/forgot-password', [RecuperacionContrasenaController::class, 'enviarEnlace'])
    ->name('password.email');

Route::get('/reset-password/{token}', [RecuperacionContrasenaController::class, 'mostrarFormularioReset'])
    ->middleware('guest')
    ->name('password.reset');

Route::post('/reset-password', [RecuperacionContrasenaController::class, 'resetear'])
    ->middleware('guest')
    ->name('password.update');

// Rutas solo para admin y superadmin
Route::middleware(['auth', 'role:admin,superadmin'])->group(function () {
    Route::get('/admin', function () {
        return 'Panel de administrador';
    })->name('admin.panel');

    Route::get('/admin/roles', [RbacController::class, 'index'])->name('admin.rbac.index');
    Route::put('/admin/roles/{id}', [RbacController::class, 'actualizar'])->name('admin.rbac.actualizar');

    Route::get('/admin/logs', fn() => view('logs.index'))->name('admin.logs');
});

// Rutas solo para usuarios autenticados
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
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

Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/', [SuperAdminController::class, 'index'])->name('index');
    Route::post('/usuarios/{id}/toggle-estado', [SuperAdminController::class, 'toggleEstado'])->name('toggleEstado');
    Route::post('/usuarios/{id}/cambiar-rol/{rol}', [SuperAdminController::class, 'cambiarRol'])->name('cambiarRol');
    Route::get('/stats/usuarios', [SuperAdminController::class, 'statsUsuarios'])->name('stats.usuarios');
    Route::get('/grafica', [SuperAdminController::class, 'grafica'])->name('grafica');
});

// La ruta temporal /login-super se elimino (issue #116): iniciaba sesion como
// superadmin sin pedir credenciales. Para entrar al panel usa /login con una
// cuenta con rol superadmin.

use App\Http\Controllers\PerfilController;

Route::middleware('auth')->group(function () {
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil');
    Route::post('/perfil', [PerfilController::class, 'actualizar'])->name('perfil.actualizar');
});

