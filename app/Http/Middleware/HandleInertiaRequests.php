<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                    'theme_preference' => $request->user()->theme_preference,
                    'timezone' => $request->user()->timezone ?: 'Europe/Amsterdam',
                    'date_format' => $request->user()->date_format ?: 'DD-MM-YYYY',
                    'time_format' => $request->user()->time_format ?: 'HH:mm:ss',
                    'passkeys' => fn () => $request->user()->passkeys()
                        ->get()
                        ->map(fn ($key) => $key->only(['id', 'name', 'last_used_at'])),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'recovery_codes' => fn () => $request->session()->get('recovery_codes'),
            ],
        ];
    }
}
