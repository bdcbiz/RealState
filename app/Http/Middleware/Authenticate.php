<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * For API routes, return null to trigger a JSON response instead of a redirect.
     */
    protected function redirectTo(Request $request): ?string
    {
        // If it's an API request, return null to send JSON response
        if ($request->is('api/*')) {
            return null;
        }

        // For web routes, redirect to login
        return route('login');
    }
}
