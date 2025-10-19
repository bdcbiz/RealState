<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request and set the application locale
     * based on the Accept-Language header or 'lang' query parameter
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get language from multiple sources (priority order):
        // 1. Query parameter (?lang=ar)
        // 2. Accept-Language header
        // 3. Default to English

        $locale = $request->get('lang')
            ?? $request->header('Accept-Language')
            ?? 'en';

        // Clean up the locale (handle cases like "ar-EG" -> "ar")
        $locale = strtolower(substr($locale, 0, 2));

        // Only allow 'ar' or 'en'
        if (!in_array($locale, ['ar', 'en'])) {
            $locale = 'en';
        }

        // Set Laravel's application locale
        app()->setLocale($locale);

        return $next($request);
    }
}
