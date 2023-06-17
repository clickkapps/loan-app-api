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
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if(config('app.env') == 'production') {

            Log::info('incoming request header: ' . json_encode($request->header()));
            Log::info('incoming request body: ' . json_encode($request->all()));
            Log::info('incoming request IP: ' . json_encode($request->ip()));
            Log::info('incoming request URL: ' . json_encode($request->url()));
            Log::info('incoming request METHOD: ' . $request->method());
            Log::info('outgoing response: ' . json_encode($response->getContent()));

        }

        return $response;
    }
}
