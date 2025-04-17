<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ContributionController;
use App\Http\Controllers\CoordinatorController;
use App\Http\Controllers\CoordinatorUserController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\GuestUserController;
use App\Http\Controllers\ManagerUserController;
use App\Http\Controllers\MarketingManagerController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StudentUserController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\TransactionLogController;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('/auth/logout', [AdminAuthController::class, 'logout']);

    //Admin Route

    Route::get('/admin/dashboard', [AdminAuthController::class, 'countData']);

    //User Management

    //Admin User Management

    Route::get('/admin/admin-users', [AdminUserController::class, 'index']);

    Route::post('/admin/admin-users/create', [AdminUserController::class, 'store']);

    Route::post('/admin/admin-users/edit', [AdminUserController::class, 'update']);

    Route::post('/admin/admin-users/{id}/delete', [AdminUserController::class, 'delete']);

    //Manager User Management

    Route::get('/admin/manager-users', [ManagerUserController::class, 'index']);

    Route::post('/admin/manager-users/create', [ManagerUserController::class, 'store']);

    Route::post('/admin/manager-users/edit', [ManagerUserController::class, 'update']);

    Route::post('/admin/manager-users/{id}/delete', [ManagerUserController::class, 'delete']);

    //Coordinator User Management

    Route::get('/admin/coordinator-users', [CoordinatorUserController::class, 'index']);

    Route::post('/admin/coordinator-users/create', [CoordinatorUserController::class, 'store']);

    Route::post('/admin/coordinator-users/edit', [CoordinatorUserController::class, 'update']);

    Route::post('/admin/coordinator-users/{id}/delete', [CoordinatorUserController::class, 'delete']);

    //Student User Management

    Route::get('/admin/student-users', [StudentUserController::class, 'index']);

    Route::post('/admin/student-users/create', [StudentUserController::class, 'store']);

    Route::post('/admin/student-users/edit', [StudentUserController::class, 'update']);

    Route::post('/admin/student-users/{id}/delete', [StudentUserController::class, 'delete']);

    //Guest User Management

    Route::get('/admin/guest-users', [GuestUserController::class, 'index']);

    Route::post('/admin/guest-users/create', [GuestUserController::class, 'store']);

    Route::post('/admin/guest-users/edit', [GuestUserController::class, 'update']);

    Route::post('/admin/guest-users/{id}/delete', [GuestUserController::class, 'delete']);

    //User Management End

    //Faculty Management

    Route::get('/admin/faculties', [FacultyController::class, 'index']);

    Route::post('/admin/faculties/create', [FacultyController::class, 'store']);

    Route::post('/admin/faculties/edit', [FacultyController::class, 'update']);

    Route::post('/admin/faculties/{id}/delete', [FacultyController::class, 'delete']);

    //Faculty Management End

    //Log Management

    Route::get('/admin/logs', [TransactionLogController::class, 'index']);

    //System Setting

    Route::get('/admin/setting', [SystemSettingController::class, 'index']);

    Route::post('/admin/setting/edit', [SystemSettingController::class, 'update']);

    //System Setting End

    //Admin Route End

    //Coordinator Route

    //View Faculty Contribution
    Route::get('/coordinator/contributions', [ContributionController::class, 'getContributionsByFacultyID']);

    //View Faculty Guest
    Route::get('/coordinator/guests', [CoordinatorController::class, 'getGuestByFacultyID']);

    //View Faculty Student
    Route::get('/coordinator/students', [CoordinatorController::class, 'getStudentByFacultyID']);

    //Select Contribution
    Route::post('/coordinator/select', [CoordinatorController::class, 'selectContribution']);

    //Review Contribution
    Route::post('/coordinator/review', [CoordinatorController::class, 'reviewContribution']);

    //View Contribution Detail
    Route::get('/coordinator/detail/{id}', [CoordinatorController::class, 'viewContributionDetail']);

    //Make Comment
    Route::post('/coordinator/comment/add', [CoordinatorController::class, 'addComment']);

    //Coordinator Route End

    // Marketing Manager Routes

    // Marketing Manager Dashboard
    Route::get('/manager/dashboard', [MarketingManagerController::class, 'index']);

    Route::get('/manager/selectedArticles', [MarketingManagerController::class, 'selectedArticles']);

    // Download Contributions as ZIP
    Route::post('/manager/download-zip', [MarketingManagerController::class, 'downloadZip']);

    // Statistics and Reports
    Route::get('/manager/statistics-reports', [MarketingManagerController::class, 'statisticsAndReports']);

    // Marketing Manager Routes End

    // Student Route

    Route::post('/student/uploadArticle', [ContributionController::class, 'uploadArticle']);

    Route::get('/student/contributions', [ContributionController::class, 'getContributionsByStudentID']);

    Route::get('/student/contributions/{id}', [ContributionController::class, 'getContributionByContributionID']);

    Route::post('/student/articles/{id}/edit', [ContributionController::class, 'editArticle']);

    Route::get('/student/articles/{id}/comments', [ContributionController::class, 'viewComments']);

    // Route::post('/student/articles/{articleId}/comments/{commentId}/respond', [ContributionController::class, 'respondToComment']);

    Route::post('/student/articles/{articleId}/comments', [ContributionController::class, 'addComment']);

    Route::get('/student/dashboard', [StudentUserController::class, 'dashboard']);

    // Student Route End

    // Guest Route Start

    Route::get('/guest/articles', [GuestController::class, 'index']);

    // Notification Route

    Route::get('/user/notifications', [NotificationController::class, 'getNotifications']);

    Route::post('/user/notification/read', [NotificationController::class, 'markAsRead']);

    // Notification Route End
});

Route::post('/auth/login', [AdminAuthController::class, 'login']);

Route::post('/auth/passwordUpdate', [AdminAuthController::class, 'passwordUpdate']);

// Route::post('/auth/login', [AdminAuthController::class, 'hello']);
