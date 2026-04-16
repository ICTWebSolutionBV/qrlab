<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactorSetup
{
    /**
     * Routes accessible while the user has not yet enrolled in any 2FA method.
     */
    protected array $allowedRouteNames = [
        'two-factor.setup',
        'two-factor.setup.totp.init',
        'two-factor.setup.totp.confirm',
        'two-factor.setup.email.enable',
        // Existing passkey routes reused by the setup page
        'passkeys.register-options',
        'passkeys.store',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->hasTwoFactorEnabled()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($routeName && in_array($routeName, $this->allowedRouteNames, true)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->header('X-Inertia')) {
            if ($request->isMethod('GET')) {
                return redirect()->route('two-factor.setup');
            }
            return back()->withErrors([
                'two_factor' => 'You must set up two-factor authentication before continuing.',
            ]);
        }

        return redirect()->route('two-factor.setup');
    }
}
