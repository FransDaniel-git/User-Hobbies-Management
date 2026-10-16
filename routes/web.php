<?php

use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('users.index');
});

// Halaman autentikasi (publik, token JWT diminta via JS ke API).
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// CRUD pengguna beserta hobinya, hanya bisa diakses dengan token JWT yang valid.
Route::resource('users', UserController::class)->except(['show'])->middleware('jwt.web');
