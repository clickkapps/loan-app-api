<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

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

        if(config('app.env') == 'production') {

            Log::info('incoming request header: ' . json_encode($request->header()));
            Log::info('incoming request body: ' . json_encode($request->all()));
            Log::info('incoming request IP: ' . json_encode($request->ip()));

            $header = $request->header();
            if(isset($header['cf-ipcountry'])){
                Log::info("Request terminated: cf-ipcountry property not available in header");
                exit;
            }
            $countryCodes = ['cf-ipcountry']; // IN
            $encodedCountryCodes = json_encode($countryCodes);
            Log::info("country code: $encodedCountryCodes");
            if(count($countryCodes) != 1 || $countryCodes[0] != "GH") {
                Log::info("Request terminated: invalid country code");
                exit;
            }
        }


        return $next($request);
    }
}
