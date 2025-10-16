<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\EmailVerificationController;

Route::middleware('guest')->group(function () {
    // Login Routes
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
    
    // Registration Routes
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);
    Route::post('register/check-email', [RegisterController::class, 'checkEmail']);
    Route::post('register/check-organization-email', [RegisterController::class, 'checkOrganizationEmail']);
    
    // Password Reset Routes
    Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::post('forgot-password/custom', [ForgotPasswordController::class, 'sendCustomResetLink']);
    
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
    Route::post('reset-password/custom', [ResetPasswordController::class, 'resetWithCustomToken']);
    Route::post('reset-password/validate-token', [ResetPasswordController::class, 'validateToken']);
});

Route::middleware('auth')->group(function () {
    // Logout Route
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
    
    // Email Verification Routes
    Route::get('email/verify', [EmailVerificationController::class, 'show'])->name('verification.notice');
    Route::get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationController::class, 'resend'])->name('verification.send');
    Route::get('email/verification-status', [EmailVerificationController::class, 'status']);
});

// Public email verification route (no auth required)
Route::get('verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify.public');

// Public password reset routes
Route::post('forgot-password/public', [ForgotPasswordController::class, 'sendCustomResetLink']);
Route::post('reset-password/public', [ResetPasswordController::class, 'resetWithCustomToken']);
Route::post('verify-email/public', [EmailVerificationController::class, 'sendVerification']);
