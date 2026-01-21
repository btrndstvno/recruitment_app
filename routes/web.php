<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\PsikotestReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

// Guest Routes (Login, Forgot Password)
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // Profile Routes
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('profile/name', [ProfileController::class, 'updateName'])->name('profile.update.name');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update.password');

    // Applicants CRUD Routes
    Route::resource('applicants', ApplicantController::class);
    Route::patch('applicants/{applicant}/status', [ApplicantController::class, 'updateStatus'])->name('applicants.updateStatus');
    Route::delete('applicants-bulk-delete', [ApplicantController::class, 'bulkDelete'])->name('applicants.bulkDelete');
    Route::post('applicants-bulk-archive', [ApplicantController::class, 'bulkArchive'])->name('applicants.bulkArchive');
    Route::post('applicants-bulk-unarchive', [ApplicantController::class, 'bulkUnarchive'])->name('applicants.bulkUnarchive');
    Route::get('applicants-all-ids', [ApplicantController::class, 'getAllIds'])->name('applicants.getAllIds');

    // Import Routes
    Route::get('import', [ImportController::class, 'showForm'])->name('import.form');
    Route::post('import', [ImportController::class, 'import'])->name('import.process');
    Route::get('import/template', [ImportController::class, 'downloadTemplate'])->name('import.template');

    // Psikotest Report Routes
    Route::prefix('applicants/{applicant}/psikotest')->name('psikotest.')->group(function () {
        Route::get('/create', [PsikotestReportController::class, 'create'])->name('create');
        Route::post('/', [PsikotestReportController::class, 'store'])->name('store');
        Route::get('/', [PsikotestReportController::class, 'show'])->name('show');
        Route::get('/edit', [PsikotestReportController::class, 'edit'])->name('edit');
        Route::put('/', [PsikotestReportController::class, 'update'])->name('update');
    });

    // Export Excel Applicants
    Route::get('applicants-export', [\App\Http\Controllers\ApplicantExportController::class, 'export'])->name('applicants.export');

    // Redirect home to applicants list
    Route::get('/', function () {
        return redirect()->route('applicants.index');
    });
});

