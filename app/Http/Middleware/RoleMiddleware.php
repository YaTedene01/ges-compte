<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Attach user permissions (scopes) and role claim to the request
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (is_object($user)) {
            $scopes = [];
            // If using Passport token
            try {
                // guard against non-object returns from token()
                $token = null;
                if (method_exists($user, 'token')) {
                    $token = $user->token();
                } elseif (method_exists($request, 'user') && is_object($request->user())) {
                    // fallback in case user() behaves differently
                    $maybeUser = $request->user();
                    $token = method_exists($maybeUser, 'token') ? $maybeUser->token() : null;
                }

                if (is_object($token) || is_array($token)) {
                    $scopes = method_exists($token, 'scopes') ? $token->scopes() : ($token->scopes ?? []);
                }
            } catch (\Exception $e) {
                // ignore
            }

            // Attach permissions and role
            $request->attributes->set('permissions', $scopes);
            $request->attributes->set('role', $user->role ?? null);
        }

        return $next($request);
    }
}
