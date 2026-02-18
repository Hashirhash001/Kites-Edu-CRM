<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EduLeadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;

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

    // Bulk Import Routes - BEFORE resource route
    Route::get('/edu-leads/bulk-import', [EduLeadController::class, 'bulkImport'])
        ->name('edu-leads.bulk-import');

    Route::get('/edu-leads/download-template', [EduLeadController::class, 'downloadTemplate'])
        ->name('edu-leads.download-template');

    Route::post('/edu-leads/process-bulk-import', [EduLeadController::class, 'processBulkImport'])
        ->name('edu-leads.process-bulk-import');

    Route::get('/edu-leads/bulk-import/progress/{import}', [EduLeadController::class, 'getImportProgress'])
        ->name('edu-leads.import-progress');

    Route::get('/edu-leads/bulk-import/download-failed/{import}', [EduLeadController::class, 'downloadFailedRows'])
        ->name('edu-leads.download-failed-rows');

    // Export - BEFORE resource route
    Route::get('/edu-leads/export', [EduLeadController::class, 'export'])
        ->name('edu-leads.export');

    // Lead Actions - BEFORE resource route
    Route::post('/edu-leads/{eduLead}/assign', [EduLeadController::class, 'assignLead'])
        ->name('edu-leads.assign');

    Route::post('/edu-leads/bulk-assign', [EduLeadController::class, 'bulkAssign'])
        ->name('edu-leads.bulk-assign');

    // ✅ FIXED: Changed route names to match views (camelCase)
    Route::post('/edu-leads/{eduLead}/calls', [EduLeadController::class, 'addCall'])
        ->name('edu-leads.addCall');  // ← Changed from add-call

    Route::post('/edu-leads/{eduLead}/followups', [EduLeadController::class, 'addFollowup'])
        ->name('edu-leads.addFollowup');  // ← Changed from add-followup

    Route::post('/edu-leads/{eduLead}/notes', [EduLeadController::class, 'addNote'])
        ->name('edu-leads.addNote');  // ← Changed from add-note

    Route::post('/edu-lead-followups/{followup}/complete', [EduLeadController::class, 'completeFollowup'])
        ->name('edu-leads.completeFollowup');  // ← Changed from complete-followup

    Route::delete('/edu-leads/followup/{followup}', [EduLeadController::class, 'deleteFollowup'])
        ->name('edu-leads.deleteFollowup');  // ← Changed from delete-followup

    Route::delete('/edu-leads/call/{call}', [EduLeadController::class, 'deleteCall'])
        ->name('edu-leads.deleteCall');  // ← Changed from delete-call

    Route::delete('/edu-leads/note/{note}', [EduLeadController::class, 'deleteNote'])
        ->name('edu-leads.deleteNote');  // ← Changed from delete-note

    // Dashboard Widget - Today's Followups
    Route::get('/edu-leads/today-followups', [EduLeadController::class, 'getTodayFollowups'])
        ->name('edu-leads.today-followups');

    // Education Lead Resource Routes - MUST BE LAST
    Route::resource('edu-leads', EduLeadController::class);

});

// ============================================================
// ROOT REDIRECT
// ============================================================
Route::redirect('/', 'dashboard');
