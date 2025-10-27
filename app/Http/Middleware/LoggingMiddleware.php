<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LoggingMiddleware
{
     /**
      * Handle an incoming request.
      *
      * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
      */
     public function handle(Request $request, Closure $next): Response
     {
         $startTime = now();

         Log::info('Operation started', [
             'method' => $request->method(),
             'url' => $request->fullUrl(),
             'host' => $request->getHost(),
             'user_agent' => $request->userAgent(),
             'ip' => $request->ip(),
             'start_time' => $startTime,
         ]);

         $response = $next($request);

         $endTime = now();

         Log::info('Operation completed', [
             'method' => $request->method(),
             'url' => $request->fullUrl(),
             'status' => $response->getStatusCode(),
             'duration' => $endTime->diffInMilliseconds($startTime),
             'end_time' => $endTime,
         ]);

         return $response;
     }
}
