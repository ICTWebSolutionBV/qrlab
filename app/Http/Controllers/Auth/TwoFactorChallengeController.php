<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TwoFactorToken;
use App\Models\User;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallengeController extends Controller
{
    public function show(Request $request)
    {
        $userId = $request->session()->get('2fa:user:id');

        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);

        return Inertia::render('Auth/TwoFactorChallenge', [
            'methods' => [
                'totp' => $user->hasTotpEnabled(),
                'email' => $user->hasEmailOtpEnabled(),
                'passkey' => $user->hasPasskeysEnabled(),
            ],
            'emailMasked' => $this->maskEmail($user->email),
        ]);
    }

    public function sendEmailCode(Request $request)
    {
        $userId = $request->session()->get('2fa:user:id');

        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);

        if (!$user->hasEmailOtpEnabled()) {
            throw ValidationException::withMessages([
                'code' => 'Email OTP is not enabled for this account.',
            ]);
        }

        // Invalidate previous tokens
        $user->twoFactorTokens()->delete();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        TwoFactorToken::query()->create([
            'user_id' => $user->id,
            'token' => hash('sha256', $code),
            'expires_at' => now()->addMinutes(10),
        ]);

        $user->notify(new TwoFactorCodeNotification($code));

        return back()->with('success', 'Verification code sent to your email.');
    }

    public function verify(Request $request)
    {
        $userId = $request->session()->get('2fa:user:id');

        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);

        $data = $request->validate([
            'method' => ['required', 'in:totp,email,recovery'],
            'code' => ['required', 'string'],
        ]);

        $ok = match ($data['method']) {
            'totp' => $this->verifyTotp($user, $data['code']),
            'email' => $this->verifyEmail($user, $data['code']),
            'recovery' => $this->verifyRecovery($user, $data['code']),
        };

        if (!$ok) {
            throw ValidationException::withMessages([
                'code' => 'The provided code is invalid or has expired.',
            ]);
        }

        $remember = (bool) $request->session()->pull('2fa:remember', false);

        Auth::login($user, $remember);
        $request->session()->forget('2fa:user:id');
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    protected function verifyTotp(User $user, string $code): bool
    {
        if (!$user->hasTotpEnabled()) {
            return false;
        }

        $google2fa = new Google2FA();

        return (bool) $google2fa->verifyKey($user->two_factor_secret, $code, 1);
    }

    protected function verifyEmail(User $user, string $code): bool
    {
        if (!$user->hasEmailOtpEnabled()) {
            return false;
        }

        $hashed = hash('sha256', $code);

        $token = $user->twoFactorTokens()
            ->where('token', $hashed)
            ->where('expires_at', '>', now())
            ->first();

        if (!$token) {
            return false;
        }

        $token->delete();

        return true;
    }

    protected function verifyRecovery(User $user, string $code): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];

        $normalized = strtolower(trim($code));

        foreach ($codes as $idx => $rc) {
            if (hash_equals(strtolower($rc), $normalized)) {
                unset($codes[$idx]);
                $user->two_factor_recovery_codes = array_values($codes);
                $user->save();
                return true;
            }
        }

        return false;
    }

    protected function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email);
        $maskedLocal = Str::limit($local, 2, '') . str_repeat('*', max(0, strlen($local) - 2));
        return $maskedLocal . '@' . $domain;
    }

    public function cancel(Request $request)
    {
        $request->session()->forget(['2fa:user:id', '2fa:remember']);
        return redirect()->route('login');
    }
}
