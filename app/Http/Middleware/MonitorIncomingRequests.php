<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Torann\GeoIP\Facades\GeoIP;

class MonitorIncomingRequests
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
        Log::info('incoming request header: ' . json_encode($request->header()));
        Log::info('incoming request body: ' . json_encode($request->all()));
        Log::info('incoming request IP: ' . json_encode($request->ip()));
        $countryCode = ip_info("Visitor", "Country Code"); // IN
        Log::info("country code: $countryCode");
//        dd($data);
        if($countryCode != "GH") {
            exit;
        }


        return $next($request);
    }
}
