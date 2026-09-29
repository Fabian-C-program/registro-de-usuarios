<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;

// Rutas para el flujo de registro

Route::get('/register', [RegisterController::class, 'create'])->name('register.create');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');