<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stevebauman\Location\Location;
use Symfony\Component\HttpFoundation\Response;

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
        $ip = $request->ip();
        $data = Location::get($ip);
//        dd($data);

        Log::info(json_encode($data));
        return $next($request);
    }
}
