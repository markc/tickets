<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotAllowedInFilament
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow access to login page for all users
        if ($request->is('admin/login')) {
            return $next($request);
        }

        // If user is authenticated
        if (auth()->check()) {
            $user = auth()->user();
            
            // If customer tries to access admin area, redirect to dashboard
            if ($user->role === 'customer') {
                return redirect('/dashboard');
            }
            
            // Allow admin and agent access
            if (in_array($user->role, ['admin', 'agent'])) {
                return $next($request);
            }
        }

        // If not authenticated or unauthorized role, block access
        abort(403, 'Unauthorized access to admin panel.');
    }
}
