<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Emails
    |--------------------------------------------------------------------------
    |
    | These email addresses have access to Horizon and Telescope dashboards
    | in non-local environments. Add your admin emails here or set the
    | ADMIN_EMAILS environment variable (comma-separated).
    |
    */

    'admin_emails' => array_filter(
        array_map('trim', explode(',', env('ADMIN_EMAILS', '')))
    ),
];
