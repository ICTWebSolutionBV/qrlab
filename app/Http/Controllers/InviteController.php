<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserInvite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class InviteController extends Controller
{
    public function show(string $token)
    {
        $invite = UserInvite::where('token', $token)->first();
        $user = Auth::user();

        // If a valid invite exists for a different logged-in user, sign them out
        // so the invitee can proceed with step 1.
        if ($invite && $invite->isValid() && $user && $user->email !== $invite->email) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('invite.show', ['token' => $token]);
        }

        // If any user is authenticated at this URL, they've completed step 1 —
        // render step 2 (works even if the invite row has been deleted).
        if ($user) {
            if ($user->hasTwoFactorEnabled()) {
                return redirect()->route('dashboard');
            }

            return Inertia::render('Auth/AcceptInvite', [
                'step' => 2,
                'token' => $token,
                'email' => $user->email,
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);
        }

        // Token unknown → hard fail.
        if (!$invite) {
            return Inertia::render('Auth/InviteInvalid', ['reason' => 'not_found']);
        }

        // Invite is used/expired, but the invitee may have started signup and
        // never finished 2FA. In that case, send them to login — the
        // `2fa.required` middleware will force them into /two-factor/setup
        // after they authenticate, so they can finish enrollment.
        if (!$invite->isValid()) {
            $existingUser = User::where('email', $invite->email)->first();

            if ($existingUser && !$existingUser->hasTwoFactorEnabled()) {
                return redirect()->route('login')
                    ->with('status', 'Please sign in with your password to finish setting up two-factor authentication.')
                    ->withInput(['email' => $invite->email]);
            }

            return Inertia::render('Auth/InviteInvalid', [
                'reason' => $invite->isUsed() ? 'used' : 'expired',
            ]);
        }

        // Valid invite, guest → step 1. But if a User already exists for this
        // email with 2FA incomplete, funnel them through login (same reason).
        $existingUser = User::where('email', $invite->email)->first();
        if ($existingUser && !$existingUser->hasTwoFactorEnabled()) {
            return redirect()->route('login')
                ->with('status', 'An account already exists for this email. Sign in to finish setting up two-factor authentication.')
                ->withInput(['email' => $invite->email]);
        }
        if ($existingUser) {
            // Fully onboarded user → invite is effectively consumed.
            UserInvite::where('email', $invite->email)->delete();
            return redirect()->route('login')
                ->with('status', 'An account already exists for this email. Please sign in.')
                ->withInput(['email' => $invite->email]);
        }

        return Inertia::render('Auth/AcceptInvite', [
            'step' => 1,
            'token' => $invite->token,
            'email' => $invite->email,
            'first_name' => $invite->first_name ?? '',
            'last_name' => $invite->last_name ?? '',
            'expires_at' => $invite->expires_at->toISOString(),
        ]);
    }

    public function accept(Request $request, string $token)
    {
        $invite = UserInvite::where('token', $token)->first();

        // Already-authenticated user with matching email just bounces to step 2.
        if (Auth::check() && $invite && Auth::user()->email === $invite->email) {
            return redirect()->route('invite.show', ['token' => $token]);
        }

        if (!$invite || !$invite->isValid()) {
            return redirect()->route('login')->withErrors(['email' => 'This invite is no longer valid.']);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (User::where('email', $invite->email)->exists()) {
            // Clean up all pending invites for this email — the account already exists.
            UserInvite::where('email', $invite->email)->delete();
            return redirect()->route('login')->withErrors(['email' => 'An account with this email already exists.']);
        }

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? null,
            'name' => trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? '')),
            'email' => $invite->email,
            'password' => Hash::make($validated['password']),
            'role' => $invite->role,
        ]);

        // Remove this invite AND any other pending invites to the same email.
        UserInvite::where('email', $invite->email)->delete();

        Auth::login($user);

        // Stay inside the wizard — GET handler now renders step 2.
        return redirect()->route('invite.show', ['token' => $token]);
    }
}
