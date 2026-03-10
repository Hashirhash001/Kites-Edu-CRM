<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EduLeadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EduLeadBulkImportController;

// ============================================================
// GUEST ROUTES (Login)
// ============================================================
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'show'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

// ============================================================
// AUTHENTICATED ROUTES
// ============================================================
Route::middleware(['auth', 'active'])->group(function () {

    // ============================================================
    // USER MANAGEMENT
    Route::get('/users/performance', [UserController::class, 'performance'])->name('users.performance');
    Route::get('/users/performance/data', [UserController::class, 'performanceData'])->name('users.performance.data');

    Route::get('/users',             [UserController::class, 'index'])->name('users.index');
    Route::post('/users',            [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/details/{type}', [UserController::class, 'details'])->name('users.details');
    Route::get('/users/{user}',      [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit']);
    Route::put('/users/{user}',      [UserController::class, 'update']);
    Route::delete('/users/{user}',   [UserController::class, 'destroy']);

    // ============================================================
    // PROFILE
    // ============================================================
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // ============================================================
    // DASHBOARD
    // ============================================================
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ============================================================
    // LOGOUT
    // ============================================================
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // ============================================================
    // EDUCATION LEADS - ALL ROUTES
    // ============================================================

    Route::patch('edu-leads/{eduLead}/status', [EduLeadController::class, 'updateStatus'])
     ->name('edu-leads.updateStatus');
    Route::patch('edu-leads/{eduLead}/tracking', [EduLeadController::class, 'updateTracking'])
     ->name('edu-leads.updateTracking');

    Route::prefix('edu-leads')->name('edu-leads.')->middleware(['auth'])->group(function () {
        Route::get('/bulk-import', [EduLeadBulkImportController::class, 'bulkImport'])->name('bulk-import');
        Route::get('/download-template', [EduLeadBulkImportController::class, 'downloadTemplate'])->name('download-template');
        Route::post('/pre-validate-import', [EduLeadBulkImportController::class, 'preValidateImport'])->name('pre-validate-import');
        Route::post('/process-bulk-import', [EduLeadBulkImportController::class, 'processBulkImport'])->name('process-bulk-import');
        Route::get('/bulk-import/progress/{id}', [EduLeadBulkImportController::class, 'getImportProgress'])->name('import-progress');
        Route::get('/download-failed/{id}', [EduLeadBulkImportController::class, 'downloadFailedRows'])->name('download-failed-rows');
    });

    Route::get('edu-leads/programs-by-type',   [EduLeadController::class, 'getProgramsByType'])->name('edu-leads.programs-by-type');
    Route::get('edu-leads/courses-by-country', [EduLeadController::class, 'getCoursesByCountry'])->name('edu-leads.courses-by-country');

    // Export - BEFORE resource route
    Route::get('/edu-leads/export', [EduLeadController::class, 'export'])
        ->name('edu-leads.export');

    // Lead Actions - BEFORE resource route
    Route::post('/edu-leads/{eduLead}/assign', [EduLeadController::class, 'assignLead'])
        ->name('edu-leads.assign');

    Route::post('/edu-leads/bulk-assign', [EduLeadController::class, 'bulkAssign'])
        ->name('edu-leads.bulk-assign');

    Route::delete('/edu-leads/bulk-delete', [EduLeadController::class, 'bulkDelete'])
    ->name('edu-leads.bulk-delete');

    Route::post('/edu-leads/{eduLead}/calls', [EduLeadController::class, 'addCall'])
        ->name('edu-leads.addCall');

    Route::post('/edu-leads/{eduLead}/followups', [EduLeadController::class, 'addFollowup'])
        ->name('edu-leads.addFollowup');

    Route::post('/edu-leads/{eduLead}/notes', [EduLeadController::class, 'addNote'])
        ->name('edu-leads.addNote');

    Route::post('edu-leads/followups/{followup}/complete', [EduLeadController::class, 'completeFollowup'])
     ->name('edu-leads.followups.complete');

    Route::put('/edu-leads/followups/{followup}', [EduLeadController::class, 'updateFollowup'])
     ->name('edu-leads.followups.update');

    Route::delete('/edu-leads/followup/{followup}', [EduLeadController::class, 'deleteFollowup'])
        ->name('edu-leads.deleteFollowup');

    Route::delete('/edu-leads/call/{call}', [EduLeadController::class, 'deleteCall'])
        ->name('edu-leads.deleteCall');

    Route::delete('/edu-leads/note/{note}', [EduLeadController::class, 'deleteNote'])
        ->name('edu-leads.deleteNote');

    // Dashboard Widget - Today's Followups
    Route::get('/edu-leads/today-followups', [EduLeadController::class, 'getTodayFollowups'])
        ->name('edu-leads.today-followups');

    // Dashboard Quick Search
    Route::get('/edu-leads/quick-search', [EduLeadController::class, 'quickSearch'])
    ->name('edu-leads.quick-search');

    // Education Lead Resource Routes
    Route::resource('edu-leads', EduLeadController::class);

});

// ============================================================
// ROOT REDIRECT
// ============================================================
Route::redirect('/', 'dashboard');
