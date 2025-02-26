<?php

use App\Http\Controllers\AccountRegisterController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\ManagerUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::middleware(['auth:sanctum'])->group(function() {

    //Admin Route

    Route::get('/admin/dashboard', [AdminAuthController::class, 'login']);

    //User Management

    //User Register End

    //Manager User Management

    Route::get('/admin/manager-users', [ManagerUserController::class, 'index']);

    Route::post('/admin/manager-users/create', [ManagerUserController::class, 'store']);

    Route::post('/admin/manager-users/edit', [ManagerUserController::class, 'update']);

    Route::post('/admin/manager-users/{id}/delete', [ManagerUserController::class, 'delete']);

    //Faculty Management

    Route::get('/admin/faculties', [FacultyController::class, 'index']);

    Route::post('/admin/faculties/create', [FacultyController::class, 'store']);

    Route::post('/admin/faculties/edit', [FacultyController::class, 'update']);

    Route::post('/admin/faculties/{id}/delete', [FacultyController::class, 'delete']);

    //Faculty Management End

    //Admin Route End
});

Route::post('/auth/login', [AdminAuthController::class, 'login']);

// Route::post('/auth/login', [AdminAuthController::class, 'hello']);