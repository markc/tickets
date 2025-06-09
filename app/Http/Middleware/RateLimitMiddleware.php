<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $key = 'global'): Response
    {
        $limiterKey = $this->resolveLimiterKey($request, $key);

        if (RateLimiter::tooManyAttempts($limiterKey, $this->getMaxAttempts($key))) {
            $retryAfter = RateLimiter::availableIn($limiterKey);

            return response()->json([
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $retryAfter,
            ], 429)->header('Retry-After', $retryAfter);
        }

        RateLimiter::increment($limiterKey, $this->getDecayTime($key));

        $response = $next($request);

        $remaining = $this->getMaxAttempts($key) - RateLimiter::attempts($limiterKey);

        return $response->withHeaders([
            'X-RateLimit-Limit' => $this->getMaxAttempts($key),
            'X-RateLimit-Remaining' => max(0, $remaining),
            'X-RateLimit-Reset' => RateLimiter::availableIn($limiterKey),
        ]);
    }

    protected function resolveLimiterKey(Request $request, string $key): string
    {
        return match ($key) {
            'tickets' => 'tickets:'.($request->user()?->id ?? $request->ip()),
            'search' => 'search:'.($request->user()?->id ?? $request->ip()),
            'login' => 'login:'.$request->ip(),
            'api' => 'api:'.($request->user()?->id ?? $request->ip()),
            default => 'global:'.$request->ip(),
        };
    }

    protected function getMaxAttempts(string $key): int
    {
        return match ($key) {
            'tickets' => 10, // 10 tickets per window
            'search' => 30,  // 30 searches per window
            'login' => 5,    // 5 login attempts per window
            'api' => 60,     // 60 API calls per window
            default => 100,  // 100 requests per window
        };
    }

    protected function getDecayTime(string $key): int
    {
        return match ($key) {
            'login' => 300,  // 5 minutes for login attempts
            'tickets' => 600, // 10 minutes for ticket creation
            default => 60,   // 1 minute for other operations
        };
    }
}
