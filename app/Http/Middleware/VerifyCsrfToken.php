<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/api/connectionTest',
        '/api/streamAlerts',
        '/api/new_event',
        '/api/delete_event',
        '/api/getInformationAboutStream'
    ];
}
