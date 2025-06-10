<?php

if (! function_exists('safe_auth_user')) {
    /**
     * Get the authenticated user, or return admin user in local environment
     */
    function safe_auth_user()
    {
        $user = auth()->user();

        // In local environment without auth, use admin user for screenshots
        if (! $user && app()->environment('local')) {
            // Use simple caching but allow cache clearing for tests
            static $adminUser = null;
            static $lastCheck = null;

            // Reset cache if environment changed or in test mode
            if ($lastCheck !== app()->environment() || app()->runningUnitTests()) {
                $adminUser = null;
                $lastCheck = app()->environment();
            }

            if ($adminUser === null) {
                $adminUser = \App\Models\User::where('email', 'admin@tikm.com')->first();
            }

            return $adminUser;
        }

        return $user;
    }
}

if (! function_exists('safe_auth_id')) {
    /**
     * Get the authenticated user ID, or return admin ID in local environment
     */
    function safe_auth_id()
    {
        return safe_auth_user()?->id;
    }
}

if (! function_exists('safe_auth_check')) {
    /**
     * Check if user is authenticated, considering local environment
     */
    function safe_auth_check()
    {
        return safe_auth_user() !== null;
    }
}
