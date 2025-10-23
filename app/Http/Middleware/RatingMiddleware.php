<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RatingMiddleware
{
     /**
      * Handle an incoming request.
      *
      * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
      */
     public function handle(Request $request, Closure $next): Response
     {
         $ip = $request->ip();
         $key = "rate_limit_{$ip}";

         $requests = Cache::get($key, 0);
         $requests++;

         if ($requests > 100) {
             return response()->json([
                 'success' => false,
                 'error' => [
                     'code' => 'ERROR_429',
                     'message' => 'Too many requests',
                     'details' => [],
                     'timestamp' => now()->toISOString(),
                     'path' => $request->path(),
                     'traceId' => uniqid(),
                 ],
             ], 429);
         }

         Cache::put($key, $requests, 60); // 1 minute

         return $next($request);
     }
}
