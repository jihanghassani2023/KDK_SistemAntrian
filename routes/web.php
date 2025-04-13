<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;

// Guest Routes
Route::get('/', [App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [App\Http\Controllers\AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register'])->name('register.submit');

/// User Routes (Authenticated)
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\UserController::class, 'dashboard'])->name('user.dashboard');
    Route::post('/take-queue', [App\Http\Controllers\UserController::class, 'takeQueueNumber'])->name('user.take-queue');
});

// Admin Routes (Authenticated)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/admin/serve-next', [App\Http\Controllers\AdminController::class, 'serveNextQueue'])->name('admin.serve-next');
    Route::post('/admin/reset-queue', [App\Http\Controllers\AdminController::class, 'resetQueue'])->name('admin.reset-queue');
});


// Logout Route
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
