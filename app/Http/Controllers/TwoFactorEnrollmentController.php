<?php

namespace App\Http\Controllers;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorEnrollmentController extends Controller
{
    public function show(Request $request)
    {
        // If already enrolled, push them to the dashboard.
        if ($request->user()->hasTwoFactorEnabled()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/InitialTwoFactorSetup', [
            'user' => [
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
        ]);
    }

    // ---- TOTP ----

    public function totpInit(Request $request)
    {
        $user = $request->user();

        if ($user->hasTotpEnabled()) {
            throw ValidationException::withMessages(['code' => 'TOTP is already enabled.']);
        }

        $google2fa = new Google2FA();

        $secret = $user->two_factor_secret ?: $google2fa->generateSecretKey();
        $user->forceFill(['two_factor_secret' => $secret])->save();

        $otpauthUrl = $google2fa->getQRCodeUrl(
            config('app.name', 'Laravel'),
            $user->email,
            $secret
        );

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

        if (empty($user->two_factor_recovery_codes)) {
            $user->two_factor_recovery_codes = collect(range(1, 8))
                ->map(fn () => Str::lower(Str::random(5)) . '-' . Str::lower(Str::random(5)))
                ->values()
                ->all();
        }

        $user->two_factor_confirmed_at = now();
        $user->save();

        return back()->with([
            'success' => 'Authenticator app enabled.',
            'recovery_codes' => $user->two_factor_recovery_codes,
        ]);
    }

    // ---- Email OTP ----

    public function emailEnable(Request $request)
    {
        $request->user()->forceFill(['two_factor_email_enabled' => true])->save();

        return back()->with('success', 'Email verification enabled.');
    }

}
