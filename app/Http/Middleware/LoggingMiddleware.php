<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\OperationLog;

class LoggingMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            $user = $request->user();
            $userId = is_object($user) ? ($user->id ?? null) : null;
            OperationLog::create([
                'user_id' => $userId,
                'method' => $request->method(),
                'path' => $request->path(),
                'payload' => json_encode($request->all()),
                'status' => $response->getStatusCode(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // don't break the request on logging failure
        }

        return $response;
    }
}

