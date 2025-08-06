<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/me', [AuthController::class, 'me']);
Route::post('auth/refresh', [AuthController::class, 'refresh']); // Ruta para refrescar el token
Route::post('auth/logout', [AuthController::class, 'logout']); // Ruta para cerrar sesión