<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only apply CSP to HTML responses
        if (! $response instanceof \Illuminate\Http\Response ||
            ! str_contains($response->headers->get('content-type', ''), 'text/html')) {
            return $response;
        }

        $csp = $this->buildContentSecurityPolicy($request);

        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        return $response;
    }

    protected function buildContentSecurityPolicy(Request $request): string
    {
        $baseUrl = $request->getSchemeAndHttpHost();
        $nonce = base64_encode(random_bytes(16));

        // Store nonce for use in views
        app()->instance('csp-nonce', $nonce);

        $directives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' cdn.jsdelivr.net unpkg.com", // Needed for Filament
            "style-src 'self' 'unsafe-inline' fonts.googleapis.com fonts.bunny.net cdn.jsdelivr.net", // Needed for Tailwind
            "font-src 'self' fonts.gstatic.com fonts.bunny.net",
            "img-src 'self' data: blob: ui-avatars.com",
            "connect-src 'self'",
            "media-src 'self'",
            "object-src 'none'",
            "child-src 'self'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "base-uri 'self'",
        ];

        // Add development-specific rules
        if (app()->environment('local')) {
            $directives[] = "script-src 'self' 'unsafe-inline' 'unsafe-eval' localhost:* 127.0.0.1:* cdn.jsdelivr.net unpkg.com";
            $directives[] = "connect-src 'self' ws://localhost:* ws://127.0.0.1:* wss://localhost:* wss://127.0.0.1:*";
        }

        return implode('; ', $directives);
    }
}
