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

        if (!$invite || !$invite->isValid()) {
            return Inertia::render('Auth/InviteInvalid', [
                'reason' => !$invite ? 'not_found' : ($invite->isUsed() ? 'used' : 'expired'),
            ]);
        }

        return Inertia::render('Auth/AcceptInvite', [
            'token' => $invite->token,
            'email' => $invite->email,
            'expires_at' => $invite->expires_at->toISOString(),
        ]);
    }

    public function accept(Request $request, string $token)
    {
        $invite = UserInvite::where('token', $token)->first();

        if (!$invite || !$invite->isValid()) {
            return redirect()->route('login')->withErrors(['email' => 'This invite is no longer valid.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (User::where('email', $invite->email)->exists()) {
            $invite->update(['used_at' => now()]);
            return redirect()->route('login')->withErrors(['email' => 'An account with this email already exists.']);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $invite->email,
            'password' => Hash::make($validated['password']),
            'role' => $invite->role,
        ]);

        $invite->update(['used_at' => now()]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Account created. You can add a passkey from your profile.');
    }
}
