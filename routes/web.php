<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\BreakdownRequestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentConfirmationController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\OfficerAssignmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

// Auth
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Breakdown requests - visible with role-scoping handled inside the controller
    Route::get('/requests', [BreakdownRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/{breakdownRequest}', [BreakdownRequestController::class, 'show'])->name('requests.show');

    // Submit request - Ministry User only
    Route::middleware('role:ministry_user')->group(function () {
        Route::get('/requests-create', [BreakdownRequestController::class, 'create'])->name('requests.create');
        Route::post('/requests-create', [BreakdownRequestController::class, 'store'])->name('requests.store');
    });

    // Step 2: IT Head assigns to Assign Officer
    Route::middleware('role:it_head')->group(function () {
        Route::post('/requests/{breakdownRequest}/assign', [AssignmentController::class, 'store'])->name('assignments.store');
    });

    // Step 3: Assign Officer forwards to Technical Officer
    Route::middleware('role:assign_officer')->group(function () {
        Route::post('/assignments/{assignment}/forward', [OfficerAssignmentController::class, 'store'])->name('officer-assignments.store');
    });

    // Step 4: Technical Officer files work report
    Route::middleware('role:technical_officer')->group(function () {
        Route::post('/officer-assignments/{officerAssignment}/report', [WorkReportController::class, 'store'])->name('work-reports.store');
    });

    // Step 5: Ministry User verifies & closes
    Route::middleware('role:ministry_user')->group(function () {
        Route::post('/work-reports/{workReport}/confirm', [DepartmentConfirmationController::class, 'store'])->name('confirmations.store');
    });

    // IT Head admin: users, departments, reports
    Route::middleware('role:it_head')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('departments', DepartmentController::class)->except(['show']);
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });
});
