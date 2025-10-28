<?php

namespace App\Http\Middleware;

use Illuminate\Session\Middleware\StartSession;

class CompanySessionMiddleware extends StartSession
{
    /**
     * Get the session configuration.
     */
    public function getSession($request)
    {
        // Set a unique session cookie name for the company panel
        config(['session.cookie' => 'company_portal_session']);

        return parent::getSession($request);
    }
}
