<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasskeyLoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\TwoFactorEnrollmentController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\PasskeyController;
use App\Http\Controllers\Admin\StatsController as AdminStatsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrBulkController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\QrExportController;
use App\Http\Controllers\TwoFactorSetupController;
use Illuminate\Support\Facades\Route;

// Passkey authentication routes
Route::get('/passkeys/authentication-options', [PasskeyLoginController::class, 'options'])->name('passkeys.authentication_options');
Route::post('/passkeys/authenticate', [PasskeyLoginController::class, 'login'])->name('passkeys.login');

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');

    // Two-factor challenge (after password step)
    Route::get('/two-factor', [TwoFactorChallengeController::class, 'show'])->name('two-factor.challenge');
    Route::post('/two-factor', [TwoFactorChallengeController::class, 'verify'])->middleware('throttle:10,1')->name('two-factor.verify');
    Route::post('/two-factor/email', [TwoFactorChallengeController::class, 'sendEmailCode'])->middleware('throttle:3,1')->name('two-factor.email.send');
    Route::post('/two-factor/cancel', [TwoFactorChallengeController::class, 'cancel'])->name('two-factor.cancel');

    // Password reset
    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:5,1')->name('password.update');

});

// Invite wizard — accessible to guests (step 1) AND freshly-authenticated users (step 2)
Route::get('/invite/{token}', [InviteController::class, 'show'])->name('invite.show');
Route::post('/invite/{token}', [InviteController::class, 'accept'])->name('invite.accept');

// Public QR code generator
Route::get('/', [QrCodeController::class, 'create'])->name('qr.create');

// Public tracking redirect
Route::get('/r/{shortCode}', [QrCodeController::class, 'track'])->name('qr.track');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Passkey management (reused by initial 2FA enrollment, so BEFORE the 2fa.required gate)
    Route::get('/passkeys/register-options', [PasskeyController::class, 'registerOptions'])->name('passkeys.register-options');
    Route::post('/passkeys', [PasskeyController::class, 'store'])->name('passkeys.store');

    // Mandatory first-time 2FA enrollment
    Route::get('/two-factor/setup', [TwoFactorEnrollmentController::class, 'show'])->name('two-factor.setup');
    Route::post('/two-factor/setup/totp', [TwoFactorEnrollmentController::class, 'totpInit'])->name('two-factor.setup.totp.init');
    Route::post('/two-factor/setup/totp/confirm', [TwoFactorEnrollmentController::class, 'totpConfirm'])->name('two-factor.setup.totp.confirm');
    Route::post('/two-factor/setup/email', [TwoFactorEnrollmentController::class, 'emailEnable'])->name('two-factor.setup.email.enable');
});

// Authenticated routes that require at least one 2FA method enabled
Route::middleware(['auth', '2fa.required'])->group(function () {
    Route::delete('/passkeys/{passkey}', [PasskeyController::class, 'destroy'])->name('passkeys.destroy');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Feedback (authenticated users only)
    Route::post('/feedback', [FeedbackController::class, 'store'])->middleware('throttle:5,1')->name('feedback.store');

    // QR Codes (save, edit, delete require auth)
    Route::post('/qr', [QrCodeController::class, 'store'])->name('qr.store');
    Route::get('/qr/{qrCode}/edit', [QrCodeController::class, 'edit'])->name('qr.edit');
    Route::get('/qr/{qrCode}/analytics', [QrCodeController::class, 'analytics'])->name('qr.analytics');
    Route::put('/qr/{qrCode}', [QrCodeController::class, 'update'])->name('qr.update');
    Route::delete('/qr/{qrCode}', [QrCodeController::class, 'destroy'])->name('qr.destroy');
    Route::post('/qr/bulk-destroy', [QrCodeController::class, 'bulkDestroy'])->name('qr.bulk-destroy');
    Route::get('/qr/{qrCode}/export/{format}', [QrExportController::class, 'download'])->name('qr.export');
    Route::get('/qr/{qrCode}/logo', [QrCodeController::class, 'logo'])->name('qr.logo');

    // Bulk import + batches
    Route::get('/qr/bulk', [QrBulkController::class, 'create'])->name('qr.bulk.create');
    Route::get('/qr/bulk/template/{type}', [QrBulkController::class, 'template'])->name('qr.bulk.template');
    Route::post('/qr/bulk/{type}/preview', [QrBulkController::class, 'preview'])->name('qr.bulk.preview');
    Route::post('/qr/bulk/{type}/discard', [QrBulkController::class, 'discard'])->name('qr.bulk.discard');
    Route::post('/qr/bulk/{type}', [QrBulkController::class, 'store'])->name('qr.bulk.store');
    Route::get('/qr/batches/{batch}', [QrBulkController::class, 'show'])->name('qr.bulk.show');
    Route::delete('/qr/batches/{batch}', [QrBulkController::class, 'destroy'])->name('qr.bulk.destroy');
    Route::get('/qr/batches/{batch}/export/{format}', [QrBulkController::class, 'exportZip'])->name('qr.bulk.export');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::put('/profile/theme', [ProfileController::class, 'updateTheme'])->name('profile.theme');
    Route::put('/profile/datetime', [ProfileController::class, 'updateDateTime'])->name('profile.datetime');

    // Two-Factor setup (profile)
    Route::post('/profile/two-factor/totp/setup', [TwoFactorSetupController::class, 'totpSetup'])->name('two-factor.totp.setup');
    Route::post('/profile/two-factor/totp/confirm', [TwoFactorSetupController::class, 'totpConfirm'])->name('two-factor.totp.confirm');
    Route::post('/profile/two-factor/totp/disable', [TwoFactorSetupController::class, 'totpDisable'])->name('two-factor.totp.disable');
    Route::post('/profile/two-factor/recovery-codes', [TwoFactorSetupController::class, 'regenerateRecoveryCodes'])->name('two-factor.recovery-codes.regenerate');
    Route::post('/profile/two-factor/email/enable', [TwoFactorSetupController::class, 'emailEnable'])->name('two-factor.email.enable');
    Route::post('/profile/two-factor/email/disable', [TwoFactorSetupController::class, 'emailDisable'])->name('two-factor.email.disable');

    // Admin routes
    // Super admin only — global stats/usage dashboard
    Route::middleware('super_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/stats', [AdminStatsController::class, 'index'])->name('stats');
    });

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', AdminUserController::class)->except(['show']);
        Route::post('/users/{user}/password-reset', [AdminUserController::class, 'sendPasswordReset'])->name('users.password-reset');
        Route::post('/users/{user}/reset-2fa', [AdminUserController::class, 'resetTwoFactor'])->name('users.reset-2fa');
        Route::post('/invites', [AdminUserController::class, 'storeInvite'])->name('invites.store');
        Route::delete('/invites/{invite}', [AdminUserController::class, 'destroyInvite'])->name('invites.destroy');
    });
});
