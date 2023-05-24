<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Torann\GeoIP\Facades\GeoIP;

class LimitRequestsByCountry
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = request()->ip();
        Log::info("IP: " . json_encode($ip));
        $countryCode = GeoIP::getLocation($ip)->iso_code;
//        dd($data);

        Log::info("country code: $countryCode");
        return $next($request);
    }
}
