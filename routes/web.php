<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpenLibraryController;

Route::get('/open-library/buscar', [OpenLibraryController::class, 'buscar'])
    ->name('open-library.buscar');

Route::post('/open-library/importar', [OpenLibraryController::class, 'importar'])
    ->name('open-library.importar');

Route::get('/', function () {
    return view('welcome');
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