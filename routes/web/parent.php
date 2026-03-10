<?php

use App\Http\Controllers\ParentPortal\DashboardController;
use App\Http\Controllers\ParentPortal\PermissionController;
use Illuminate\Support\Facades\Route;

Route::prefix('parent')->name('parent.')->middleware('role:parent')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('/permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
});
