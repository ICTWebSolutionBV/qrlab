<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use Spatie\LaravelPasskeys\Models\Concerns\InteractsWithPasskeys;

#[Fillable(['name', 'first_name', 'last_name', 'email', 'password', 'role', 'theme_preference', 'timezone', 'date_format', 'time_format', 'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at', 'two_factor_email_enabled'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable implements HasPasskeys
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, InteractsWithPasskeys;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_email_enabled' => 'boolean',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function qrCodes()
    {
        return $this->hasMany(QrCode::class);
    }

    public function twoFactorTokens()
    {
        return $this->hasMany(TwoFactorToken::class);
    }

    public function hasTotpEnabled(): bool
    {
        return !is_null($this->two_factor_secret) && !is_null($this->two_factor_confirmed_at);
    }

    public function hasEmailOtpEnabled(): bool
    {
        return (bool) $this->two_factor_email_enabled;
    }

    public function hasPasskeysEnabled(): bool
    {
        return $this->passkeys()->exists();
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->hasTotpEnabled() || $this->hasEmailOtpEnabled() || $this->hasPasskeysEnabled();
    }
}
