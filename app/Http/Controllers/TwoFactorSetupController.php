<?php

namespace App\Http\Controllers;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorSetupController extends Controller
{
    // ---- TOTP ----

    public function totpSetup(Request $request)
    {
        $user = $request->user();

        $google2fa = new Google2FA();

        // Generate a new secret only if none exists or not yet confirmed
        if (!$user->two_factor_secret || $user->hasTotpEnabled()) {
            if ($user->hasTotpEnabled()) {
                throw ValidationException::withMessages([
                    'totp' => 'TOTP is already enabled. Disable it first.',
                ]);
            }
            $secret = $google2fa->generateSecretKey();
            $user->forceFill(['two_factor_secret' => $secret])->save();
        } else {
            $secret = $user->two_factor_secret;
        }

        $appName = config('app.name', 'Laravel');
        $otpauthUrl = $google2fa->getQRCodeUrl($appName, $user->email, $secret);

        $builder = new Builder(
            writer: new SvgWriter(),
            data: $otpauthUrl,
            size: 240,
            margin: 0,
        );
        $result = $builder->build();

        return response()->json([
            'secret' => $secret,
            'qr_svg' => $result->getString(),
            'otpauth_url' => $otpauthUrl,
        ]);
    }

    public function totpConfirm(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        if (!$user->two_factor_secret) {
            throw ValidationException::withMessages([
                'code' => 'No pending TOTP setup found. Start again.',
            ]);
        }

        $google2fa = new Google2FA();

        if (!$google2fa->verifyKey($user->two_factor_secret, $data['code'], 1)) {
            throw ValidationException::withMessages([
                'code' => 'Invalid verification code.',
            ]);
        }

        // Generate recovery codes if none exist
        if (empty($user->two_factor_recovery_codes)) {
            $user->two_factor_recovery_codes = $this->generateRecoveryCodes();
        }

        $user->two_factor_confirmed_at = now();
        $user->save();

        return back()->with([
            'success' => 'Two-factor authentication (TOTP) enabled.',
            'recovery_codes' => $user->two_factor_recovery_codes,
        ]);
    }

    public function totpDisable(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'password' => ['required'],
        ]);

        if (!Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'The password is incorrect.',
            ]);
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return back()->with('success', 'Two-factor authentication (TOTP) disabled.');
    }

    public function regenerateRecoveryCodes(Request $request)
    {
        $user = $request->user();

        if (!$user->hasTotpEnabled()) {
            throw ValidationException::withMessages([
                'codes' => 'TOTP is not enabled.',
            ]);
        }

        $user->two_factor_recovery_codes = $this->generateRecoveryCodes();
        $user->save();

        return back()->with([
            'success' => 'Recovery codes regenerated.',
            'recovery_codes' => $user->two_factor_recovery_codes,
        ]);
    }

    // ---- Email OTP ----

    public function emailEnable(Request $request)
    {
        $request->user()->forceFill(['two_factor_email_enabled' => true])->save();

        return back()->with('success', 'Email verification enabled.');
    }

    public function emailDisable(Request $request)
    {
        $request->validate([
            'password' => ['required'],
        ]);

        if (!Hash::check($request->input('password'), $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => 'The password is incorrect.',
            ]);
        }

        $request->user()->forceFill(['two_factor_email_enabled' => false])->save();
        $request->user()->twoFactorTokens()->delete();

        return back()->with('success', 'Email verification disabled.');
    }

    protected function generateRecoveryCodes(): array
    {
        return collect(range(1, 8))
            ->map(fn () => Str::lower(Str::random(5)) . '-' . Str::lower(Str::random(5)))
            ->values()
            ->all();
    }
}
