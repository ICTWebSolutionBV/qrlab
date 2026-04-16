<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\LaravelPasskeys\Actions\FindPasskeyToAuthenticateAction;
use Spatie\LaravelPasskeys\Support\Config;

class PasskeyLoginController extends Controller
{
    public function options()
    {
        $action = Config::getAction(
            'generate_passkey_authentication_options',
            \Spatie\LaravelPasskeys\Actions\GeneratePasskeyAuthenticationOptionsAction::class
        );

        $options = $action->execute();

        session()->put('passkey-authentication-options', $options);

        return $options;
    }

    public function login(Request $request)
    {
        $request->validate([
            'start_authentication_response' => ['required', 'json'],
        ]);

        $findPasskey = Config::getAction(
            'find_passkey',
            FindPasskeyToAuthenticateAction::class
        );

        $passkey = $findPasskey->execute(
            $request->input('start_authentication_response'),
            session()->get('passkey-authentication-options'),
        );

        if (! $passkey || ! $passkey->authenticatable) {
            return back()->withErrors(['passkey' => 'Passkey authentication failed.']);
        }

        auth()->login($passkey->authenticatable, $request->boolean('remember'));
        session()->regenerate();

        $passkey->update(['last_used_at' => now()]);

        return redirect()->intended(route('dashboard'));
    }
}
