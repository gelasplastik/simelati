<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PublicPortal\PublicStudentPermissionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login.form');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('throttle:20,1')->group(function () {
    Route::get('/izin-siswa', [PublicStudentPermissionController::class, 'create'])->name('public.permissions.create');
    Route::post('/izin-siswa', [PublicStudentPermissionController::class, 'store'])->name('public.permissions.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    require __DIR__.'/web/admin.php';
    require __DIR__.'/web/teacher.php';
    require __DIR__.'/web/parent.php';
});
