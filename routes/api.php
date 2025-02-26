<?php

use App\Http\Controllers\AdminAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::middleware(['auth:sanctum'])->group(function() {
    Route::get('/admin/dashboard', [AdminAuthController::class, 'login']);
});

Route::post('/auth/login', [AdminAuthController::class, 'login']);

// Route::post('/auth/login', [AdminAuthController::class, 'hello']);