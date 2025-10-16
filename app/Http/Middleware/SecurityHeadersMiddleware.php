<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Security Headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        // Content Security Policy
        $csp = $this->getContentSecurityPolicy();
        $response->headers->set('Content-Security-Policy', $csp);
        
        // Strict Transport Security (HTTPS only)
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }

    /**
     * Get Content Security Policy
     */
    private function getContentSecurityPolicy(): string
    {
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://checkout.razorpay.com https://cdn.jsdelivr.net localhost:5173 127.0.0.1:5173 localhost:5174 127.0.0.1:5174",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net localhost:5173 127.0.0.1:5173 localhost:5174 127.0.0.1:5174",
            "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net localhost:5173 127.0.0.1:5173 localhost:5174 127.0.0.1:5174",
            "img-src 'self' data: https: blob:",
            "connect-src 'self' https://api.razorpay.com https://checkout.razorpay.com localhost:5173 127.0.0.1:5173 localhost:5174 127.0.0.1:5174 ws://localhost:5173 ws://127.0.0.1:5173 ws://localhost:5174 ws://127.0.0.1:5174 http://127.0.0.1:8000",
            "frame-src 'self' https://checkout.razorpay.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'"
        ];

        // Only add upgrade-insecure-requests in production
        if (app()->environment('production')) {
            $csp[] = "upgrade-insecure-requests";
        }

        return implode('; ', $csp);
    }
}
