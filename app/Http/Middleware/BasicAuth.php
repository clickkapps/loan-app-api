<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class BasicAuth
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {

        $AUTH_USER = config('custom.basic_auth_username');
        $AUTH_PASS = config('custom.basic_auth_password');

        if (blank($AUTH_USER) || blank($AUTH_PASS)) {
            header('HTTP/1.1 401 Authorization Required');
            header('WWW-Authenticate: Basic realm="Access denied"');
            exit;
        }

        header('Cache-Control: no-cache, must-revalidate, max-age=0');
        $has_supplied_credentials = !(empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));
        $is_not_authenticated = (
            !$has_supplied_credentials ||
            $_SERVER['PHP_AUTH_USER'] != $AUTH_USER ||
            $_SERVER['PHP_AUTH_PW'] != $AUTH_PASS
        );
        if ($is_not_authenticated) {
            header('HTTP/1.1 401 Authorization Required');
            header('WWW-Authenticate: Basic realm="Access denied"');
            Log::info('REQUEST TERMINATED: basic auth credentials unavailable');
            exit;
        }

        return $next($request);
    }
}
