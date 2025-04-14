<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\CheckRole;

// Guest Routes
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

// User Routes
Route::middleware(['auth', CheckRole::class . ':user'])->group(function () {
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
    Route::get('/take-queue', [UserController::class, 'takeQueuePage'])->name('user.take-queue.page');
    Route::post('/take-queue', [UserController::class, 'takeQueue'])->name('user.take-queue');
    Route::get('/served-number', [UserController::class, 'servedNumber'])->name('user.served-number');
});

// Admin Routes
Route::middleware(['auth', CheckRole::class . ':admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/admin/serve-next', [AdminController::class, 'serveNextQueue'])->name('admin.serve-next');
    Route::post('/admin/reset-queue', [AdminController::class, 'resetQueue'])->name('admin.reset-queue');
    Route::get('/admin/queue-data', [AdminController::class, 'getQueueData'])->name('admin.queue-data');
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
