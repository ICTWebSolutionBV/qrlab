<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();

        return Inertia::render('Profile/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'theme_preference' => $user->theme_preference,
                'two_factor' => [
                    'totp_enabled' => $user->hasTotpEnabled(),
                    'email_enabled' => $user->hasEmailOtpEnabled(),
                    'passkeys_enabled' => $user->hasPasskeysEnabled(),
                ],
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $request->user()->id],
        ]);

        $request->user()->update($data);

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        if (!Hash::check($data['current_password'], $request->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        $request->user()->update([
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('success', 'Password changed.');
    }

    public function updateTheme(Request $request)
    {
        $data = $request->validate([
            'theme_preference' => ['required', 'in:light,dark,auto'],
        ]);

        $request->user()->update($data);

        return back()->with('success', 'Theme preference saved.');
    }
}
