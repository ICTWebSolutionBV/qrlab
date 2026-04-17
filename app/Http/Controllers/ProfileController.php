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
                'timezone' => $user->timezone ?: 'Europe/Amsterdam',
                'date_format' => $user->date_format ?: 'DD-MM-YYYY',
                'time_format' => $user->time_format ?: 'HH:mm:ss',
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

    public function updateDateTime(Request $request)
    {
        $data = $request->validate([
            'timezone' => ['required', 'string', 'timezone'],
            'date_format' => ['required', 'string', 'in:DD-MM-YYYY,DD/MM/YYYY,MM/DD/YYYY,YYYY-MM-DD,D MMM YYYY,MMM D YYYY'],
            'time_format' => ['required', 'string', 'in:HH:mm:ss,HH:mm,hh:mm:ss A,hh:mm A'],
        ]);

        $request->user()->update($data);

        return back()->with('success', 'Date & time preferences saved.');
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
