<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\FavoritoController;

Route::middleware('auth')->group(function () {
    Route::get('/favoritos', [FavoritoController::class, 'index'])->name('favoritos');
    Route::post('/favoritos/{libro}/toggle', [FavoritoController::class, 'toggle'])->name('favoritos.toggle');
});

Route::get('/login', function () {
    return 'Login pendiente - Joel está trabajando en esto';
})->name('login');

Route::get('/register', function () {
    return 'Registro pendiente - Joel está trabajando en esto';
})->name('register');

Route::get('/logout', function () {
    return 'Logout pendiente - Joel está trabajando en esto';
})->name('logout');

Route::get('/login-test', function () {
    $usuario = \App\Models\User::where('email', 'jorge@test.com')->first();
    auth()->login($usuario);
    return redirect('/favoritos');
})->name('login-test');