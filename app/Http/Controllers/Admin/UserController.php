<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\UserInviteMail;
use App\Models\User;
use App\Models\UserInvite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => User::latest()->get()->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at->toISOString(),
                'two_factor' => [
                    'totp_enabled' => $user->hasTotpEnabled(),
                    'email_enabled' => $user->hasEmailOtpEnabled(),
                    'passkeys_enabled' => $user->hasPasskeysEnabled(),
                ],
            ]),
            'invites' => UserInvite::with('inviter')->latest()->get()->map(fn ($invite) => [
                'id' => $invite->id,
                'email' => $invite->email,
                'role' => $invite->role,
                'inviter' => $invite->inviter?->name,
                'expires_at' => $invite->expires_at->toISOString(),
                'used_at' => $invite->used_at?->toISOString(),
                'is_valid' => $invite->isValid(),
            ]),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Users/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', Password::defaults(), 'confirmed'],
            'role' => ['required', 'in:user,admin'],
        ]);

        User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'name' => trim($data['first_name'] . ' ' . ($data['last_name'] ?? '')),
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'editUser' => [
                'id' => $user->id,
                'first_name' => $user->first_name ?? '',
                'last_name' => $user->last_name ?? '',
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:user,admin'],
        ]);

        $user->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'name' => trim($data['first_name'] . ' ' . ($data['last_name'] ?? '')),
            'email' => $data['email'],
            'role' => $data['role'],
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user, Request $request)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    public function storeInvite(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'role' => ['required', 'in:user,admin'],
            'expires_hours' => ['required', 'integer', 'min:1', 'max:720'],
        ]);

        $invite = UserInvite::create([
            'email' => $data['email'],
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'token' => Str::random(64),
            'role' => $data['role'],
            'invited_by' => $request->user()->id,
            'expires_at' => now()->addHours($data['expires_hours']),
        ]);

        Mail::to($invite->email)->send(new UserInviteMail($invite));

        return back()->with('success', 'Invite sent.');
    }

    public function destroyInvite(UserInvite $invite)
    {
        $invite->delete();

        return back()->with('success', 'Invite revoked.');
    }

    public function sendPasswordReset(User $user)
    {
        $status = PasswordBroker::sendResetLink(['email' => $user->email]);

        if ($status === PasswordBroker::RESET_LINK_SENT) {
            return back()->with('success', "Password reset email sent to {$user->email}.");
        }

        return back()->with('error', 'Could not send password reset email: ' . __($status));
    }

    public function resetTwoFactor(User $user, Request $request)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Use your profile to change your own 2FA settings.');
        }

        // Clear TOTP + email OTP + pending email tokens + passkeys
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_email_enabled' => false,
        ])->save();

        $user->twoFactorTokens()->delete();
        $user->passkeys()->delete();

        return back()->with('success', "Two-factor settings reset for {$user->email}. They'll be required to enroll again on next sign in.");
    }
}
