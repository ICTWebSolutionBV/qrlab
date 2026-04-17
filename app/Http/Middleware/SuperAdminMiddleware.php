<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()?->isSuperAdmin()) {
            abort(403, 'Super admin access required.');
        }

        return $next($request);
    }
}
