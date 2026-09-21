<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BreakdownRequestController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentConfirmationController;
use App\Http\Controllers\EscalationController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NotificationReadController;
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

Route::get(
    '/',
    fn () => redirect()->route('login')
);


/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::get(
    '/login',
    [LoginController::class, 'showLoginForm']
)->name('login');


Route::post(
    '/login',
    [LoginController::class, 'login']
)->name('login.attempt');


Route::post(
    '/logout',
    [LoginController::class, 'logout']
)->name('logout');


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Location Lookup Routes
    |--------------------------------------------------------------------------
    |
    | Used by Floor -> Division -> Area dependent dropdowns.
    | Available to authenticated users.
    |
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

    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Breakdown Requests
    |--------------------------------------------------------------------------
    |
    | All authenticated users can access the request list/details.
    | Visibility is controlled inside BreakdownRequestController.
    |
    */

    Route::get(
        '/requests',
        [BreakdownRequestController::class, 'index']
    )->name('requests.index');


    Route::get(
        '/requests/{breakdownRequest}',
        [BreakdownRequestController::class, 'show']
    )->name('requests.show');


    /*
    |--------------------------------------------------------------------------
    | Notification Read
    |--------------------------------------------------------------------------
    |
    | Technical Officer, Assign Officer and IT Administrator use this.
    | NotificationReadController performs the role check.
    |
    */

    Route::post(
        '/notifications/read',
        [NotificationReadController::class, 'markAsRead']
    )->name('notifications.read');


    /*
    |--------------------------------------------------------------------------
    | Ministry / Department User
    |--------------------------------------------------------------------------
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
    */

    Route::middleware('role:assign_officer')->group(function () {

        Route::post(
            '/requests/{breakdownRequest}/assign-technician',
            [OfficerAssignmentController::class, 'store']
        )->name('officer-assignments.store');


        Route::post(
            '/requests/{breakdownRequest}/forward-to-it-admin',
            [EscalationController::class, 'forward']
        )->name('escalations.forward');

    });


    /*
    |--------------------------------------------------------------------------
    | Technical Officer
    |--------------------------------------------------------------------------
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
    | Super Admin can manage:
    | - Users
    | - Categories
    | - Locations
    |
    */

    Route::middleware('role:sup_admin')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Location Management
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/locations',
            [LocationController::class, 'index']
        )->name('locations.index');


        Route::post(
            '/locations/floors',
            [LocationController::class, 'storeFloor']
        )->name('locations.floors.store');


        Route::put(
            '/locations/floors/{floor}',
            [LocationController::class, 'updateFloor']
        )->name('locations.floors.update');


        Route::post(
            '/locations/divisions',
            [LocationController::class, 'storeDivision']
        )->name('locations.divisions.store');


        Route::put(
            '/locations/divisions/{division}',
            [LocationController::class, 'updateDivision']
        )->name('locations.divisions.update');


        Route::post(
            '/locations/areas',
            [LocationController::class, 'storeArea']
        )->name('locations.areas.store');


        Route::put(
            '/locations/areas/{area}',
            [LocationController::class, 'updateArea']
        )->name('locations.areas.update');


        /*
        |--------------------------------------------------------------------------
        | User Management
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'users',
            UserController::class
        )->except([
            'show',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Category Management
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/categories',
            [CategoryController::class, 'index']
        )->name('categories.index');


        Route::get(
            '/categories/create',
            [CategoryController::class, 'create']
        )->name('categories.create');


        Route::post(
            '/categories',
            [CategoryController::class, 'store']
        )->name('categories.store');


        Route::get(
            '/categories/{category}/edit',
            [CategoryController::class, 'edit']
        )->name('categories.edit');


        Route::put(
            '/categories/{category}',
            [CategoryController::class, 'update']
        )->name('categories.update');

    });


    /*
    |--------------------------------------------------------------------------
    | Administrator
    |--------------------------------------------------------------------------
    |
    | Includes the IT Administrator.
    |
    */

    Route::middleware('role:administrator')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Reports
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/reports',
            [ReportController::class, 'index']
        )->name('reports.index');


        Route::get(
            '/reports/export/pdf',
            [ReportController::class, 'exportPdf']
        )->name('reports.export.pdf');


        /*
        |--------------------------------------------------------------------------
        | Escalated Cases
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/escalations',
            [EscalationController::class, 'index']
        )->name('escalations.index');


        Route::get(
            '/escalations/{escalation}',
            [EscalationController::class, 'show']
        )->name('escalations.show');


        Route::post(
            '/escalations/{escalation}/decision',
            [EscalationController::class, 'decide']
        )->name('escalations.decide');


        /*
        |--------------------------------------------------------------------------
        | Formal Escalation Report PDF
        |--------------------------------------------------------------------------
        |
        | EscalationController also verifies that the IT Administrator
        | has completed the decision before allowing PDF generation.
        |
        */

        Route::get(
            '/escalations/{escalation}/formal-report/pdf',
            [EscalationController::class, 'exportFormalReport']
        )->name('escalations.formal-report.pdf');

    });

});