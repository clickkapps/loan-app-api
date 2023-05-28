<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stevebauman\Location\Facades\Location;
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

            $ip = $request->ip();
            Log::info('incoming request header: ' . json_encode($request->header()));
            Log::info('incoming request body: ' . json_encode($request->all()));
            Log::info('incoming request IP: ' . json_encode($ip));


            $currentRequestInfo = Location::get($ip);

            $encodedCurrentRequestInfo = json_encode($currentRequestInfo);

            Log::info("country code: $encodedCurrentRequestInfo");

            if($currentRequestInfo) {
                if($currentRequestInfo->{'countryCode'} != "GH") {
                    Log::info("Request terminated: invalid country code");
                    exit;
                }
            }else {
                Log::info("unable to detect request information");
            }

        }


        return $next($request);
    }
}
