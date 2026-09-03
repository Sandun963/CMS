<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BreakdownRequestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentConfirmationController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OfficerAssignmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkReportController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('login'));


/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->name('login.attempt');

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout');


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Location Routes
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/locations/floors/{floor}/divisions',
        [LocationController::class, 'divisions']
    )->name('locations.divisions');

    Route::get(
        '/locations/divisions/{division}/areas',
        [LocationController::class, 'areas']
    )->name('locations.areas');


    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Breakdown Requests
    |--------------------------------------------------------------------------
    |
    | All authenticated users can access the request list/details.
    | What they can see is controlled inside BreakdownRequestController.
    |
    */

    Route::get('/requests', [BreakdownRequestController::class, 'index'])
        ->name('requests.index');

    Route::get('/requests/{breakdownRequest}', [BreakdownRequestController::class, 'show'])
        ->name('requests.show');


    /*
    |--------------------------------------------------------------------------
    | Ministry User
    |--------------------------------------------------------------------------
    |
    | Ministry Users can create breakdown requests and confirm completed work.
    |
    */

    Route::middleware('role:ministry_user')->group(function () {

        Route::get(
            '/requests-create',
            [BreakdownRequestController::class, 'create']
        )->name('requests.create');

        Route::post(
            '/requests-create',
            [BreakdownRequestController::class, 'store']
        )->name('requests.store');


        Route::post(
            '/work-reports/{workReport}/confirm',
            [DepartmentConfirmationController::class, 'store']
        )->name('confirmations.store');

    });


    /*
    |--------------------------------------------------------------------------
    | Assign Officer
    |--------------------------------------------------------------------------
    |
    | Assign Officers assign breakdown requests to Technical Officers.
    |
    */

    Route::middleware('role:assign_officer')->group(function () {

        Route::post(
            '/requests/{breakdownRequest}/assign-technician',
            [OfficerAssignmentController::class, 'store']
        )->name('officer-assignments.store');

    });


    /*
    |--------------------------------------------------------------------------
    | Technical Officer
    |--------------------------------------------------------------------------
    |
    | Technical Officers start assigned work and submit work reports.
    |
    */

    Route::middleware('role:technical_officer')->group(function () {

        Route::post(
            '/officer-assignments/{officerAssignment}/start',
            [WorkReportController::class, 'startWork']
        )->name('work-reports.start');

        Route::post(
            '/officer-assignments/{officerAssignment}/report',
            [WorkReportController::class, 'store']
        )->name('work-reports.store');

    });


    /*
    |--------------------------------------------------------------------------
    | Super Admin
    |--------------------------------------------------------------------------
    |
    | Only Super Admin can manage system user accounts.
    |
    | Super Admin can:
    | - View users
    | - Create users
    | - Edit users
    | - Change roles
    | - Activate/deactivate users
    |
    */

    Route::middleware('role:sup_admin')->group(function () {

        Route::resource('users', UserController::class)
            ->except(['show']);

    });


    /*
    |--------------------------------------------------------------------------
    | Administrator
    |--------------------------------------------------------------------------
    |
    | IT Head does NOT manage user accounts.
    |
    | IT Head can:
    | - View requests
    | - View reports
    | - Filter reports
    | - Export reports
    |
    */

    Route::middleware('role:administrator')->group(function () {

        Route::get(
            '/reports',
            [ReportController::class, 'index']
        )->name('reports.index');

        Route::get(
            '/reports/export/pdf',
            [ReportController::class, 'exportPdf']
        )->name('reports.export.pdf');

    });

});