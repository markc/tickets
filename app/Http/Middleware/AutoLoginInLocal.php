<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AutoLoginInLocal
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Auto-login as admin in local development mode
        if (app()->environment('local') && ! auth()->check()) {
            $adminUser = \App\Models\User::where('email', 'admin@tikm.com')->first();
            if ($adminUser) {
                auth()->login($adminUser);
            }
        }

        return $next($request);
    }
}
