<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceTypeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitStatisticController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Khosamphi Hospital Analytics Dashboard
|--------------------------------------------------------------------------
*/

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes
Route::middleware('auth')->group(function () {
    // Root redirect
    Route::get('/', fn() => redirect()->route('dashboard'));

    // 1. Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/analytics-data', [DashboardController::class, 'getAnalyticsData'])->name('dashboard.data');

    // 2. Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // 3. Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/excel', [ReportController::class, 'exportExcel'])->name('excel');
        Route::get('/pdf', [ReportController::class, 'exportPdf'])->name('pdf');
        Route::get('/print', [ReportController::class, 'print'])->name('print');
    });

    // 4. Visit Statistics CRUD (Viewer can index/show; Staff & Admin can create/edit/delete)
    Route::resource('visit-statistics', VisitStatisticController::class);

    // 5. Imports (Staff & Admin)
    Route::prefix('imports')->name('imports.')->group(function () {
        Route::get('/', [ImportController::class, 'index'])->name('index');
        Route::post('/preview', [ImportController::class, 'preview'])->name('preview');
        Route::post('/confirm', [ImportController::class, 'confirm'])->name('confirm');
        Route::get('/batches', [ImportController::class, 'batches'])->name('batches');
        Route::post('/batches/{batch}/rollback', [ImportController::class, 'rollback'])->name('rollback');
        Route::get('/template/{format?}', [ImportController::class, 'downloadTemplate'])->name('template');
    });

    // 6. Admin Only Modules
    Route::middleware('role:admin')->group(function () {
        Route::resource('departments', DepartmentController::class);
        Route::resource('service-types', ServiceTypeController::class);
        Route::resource('users', UserController::class);

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
    });
});

