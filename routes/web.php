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
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('password.request');

Route::post('/forgot-password', function () {
    return back()->with('status', 'Te enviamos el enlace a tu correo.');
})->name('password.email');