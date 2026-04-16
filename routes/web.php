<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasskeyLoginController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\PasskeyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\QrExportController;
use Illuminate\Support\Facades\Route;

// Passkey authentication routes
Route::get('/passkeys/authentication-options', [PasskeyLoginController::class, 'options'])->name('passkeys.authentication_options');
Route::post('/passkeys/authenticate', [PasskeyLoginController::class, 'login'])->name('passkeys.login');

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/invite/{token}', [InviteController::class, 'show'])->name('invite.show');
    Route::post('/invite/{token}', [InviteController::class, 'accept'])->name('invite.accept');
});

// Public QR code generator
Route::get('/', [QrCodeController::class, 'create'])->name('qr.create');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Passkey management
    Route::get('/passkeys/register-options', [PasskeyController::class, 'registerOptions'])->name('passkeys.register-options');
    Route::post('/passkeys', [PasskeyController::class, 'store'])->name('passkeys.store');
    Route::delete('/passkeys/{passkey}', [PasskeyController::class, 'destroy'])->name('passkeys.destroy');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // QR Codes (save, edit, delete require auth)
    Route::post('/qr', [QrCodeController::class, 'store'])->name('qr.store');
    Route::get('/qr/{qrCode}/edit', [QrCodeController::class, 'edit'])->name('qr.edit');
    Route::put('/qr/{qrCode}', [QrCodeController::class, 'update'])->name('qr.update');
    Route::delete('/qr/{qrCode}', [QrCodeController::class, 'destroy'])->name('qr.destroy');
    Route::get('/qr/{qrCode}/export/{format}', [QrExportController::class, 'download'])->name('qr.export');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::put('/profile/theme', [ProfileController::class, 'updateTheme'])->name('profile.theme');

    // Admin routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', AdminUserController::class)->except(['show']);
        Route::post('/invites', [AdminUserController::class, 'storeInvite'])->name('invites.store');
        Route::delete('/invites/{invite}', [AdminUserController::class, 'destroyInvite'])->name('invites.destroy');
    });
});
