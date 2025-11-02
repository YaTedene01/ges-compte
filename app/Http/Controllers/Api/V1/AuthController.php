<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;

class AuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * Login using Passport Password Grant (requires PASSPORT_PASSWORD_CLIENT_ID & SECRET in env)
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'scope' => 'sometimes|string'
        ]);

        $params = [
            'grant_type' => 'password',
            'client_id' => env('PASSPORT_PASSWORD_CLIENT_ID'),
            'client_secret' => env('PASSPORT_PASSWORD_CLIENT_SECRET'),
            'username' => $request->email,
            'password' => $request->password,
            'scope' => $request->get('scope', '')
        ];

        // Dispatch the token request internally to avoid external HTTP call (prevents deadlocks
        // when using the PHP built-in server or single-threaded environments).
        $tokenRequest = \Illuminate\Http\Request::create('/oauth/token', 'POST', $params);
        $tokenResponse = app()->handle($tokenRequest);

        $status = $tokenResponse->getStatusCode();
        $body = $tokenResponse->getContent();

        if ($status >= 400) {
            // body may be HTML or JSON
            return $this->errorResponse('Échec de l\'authentification', 401, ['body' => $body]);
        }

        $data = json_decode($body, true) ?: [];

        // Store tokens in secure HttpOnly cookies
        $accessToken = $data['access_token'] ?? null;
        $refreshToken = $data['refresh_token'] ?? null;

        $secureCookie = app()->environment('production');
        $sameSite = 'lax';

        // Use expires_in from the token response (seconds) to set cookie lifetime in minutes when available
        $accessMinutes = 60; // fallback 1 hour
        if (isset($data['expires_in']) && is_numeric($data['expires_in'])) {
            $accessMinutes = (int) ceil($data['expires_in'] / 60);
        }

        $cookieAccess = cookie('access_token', $accessToken, $accessMinutes, null, null, $secureCookie, true, false, $sameSite);
        // refresh token: keep for 30 days by default
        $cookieRefresh = cookie('refresh_token', $refreshToken, 60 * 24 * 30, null, null, $secureCookie, true, false, $sameSite);

        return $this->successResponse($data, 'Authentification réussie')
            ->withCookie($cookieAccess)
            ->withCookie($cookieRefresh);
    }

    /**
     * Refresh access token using refresh token grant
     */
    public function refresh(Request $request)
    {
    $refreshToken = $request->cookie('refresh_token') ?? $request->input('refresh_token');

        if (!$refreshToken) {
            return $this->errorResponse('Refresh token manquant', 400);
        }

        $params = [
            'grant_type' => 'refresh_token',
            'client_id' => env('PASSPORT_PASSWORD_CLIENT_ID'),
            'client_secret' => env('PASSPORT_PASSWORD_CLIENT_SECRET'),
            'refresh_token' => $refreshToken,
        ];

        // Dispatch internally to avoid external HTTP call
        $tokenRequest = \Illuminate\Http\Request::create('/oauth/token', 'POST', $params);
        $tokenResponse = app()->handle($tokenRequest);

        $status = $tokenResponse->getStatusCode();
        $body = $tokenResponse->getContent();

        if ($status >= 400) {
            return $this->errorResponse('Impossible de renouveler le token', 401, ['body' => $body]);
        }

        $data = json_decode($body, true) ?: [];


        $secureCookie = app()->environment('production');
        $sameSite = 'lax';
        $accessMinutes = 60;
        if (isset($data['expires_in']) && is_numeric($data['expires_in'])) {
            $accessMinutes = (int) ceil($data['expires_in'] / 60);
        }

        $cookieAccess = cookie('access_token', $data['access_token'] ?? null, $accessMinutes, null, null, $secureCookie, true, false, $sameSite);
        $cookieRefresh = cookie('refresh_token', $data['refresh_token'] ?? $refreshToken, 60 * 24 * 30, null, null, $secureCookie, true, false, $sameSite);

        return $this->successResponse($data, 'Token renouvelé')
            ->withCookie($cookieAccess)
            ->withCookie($cookieRefresh);
    }

    /**
     * Logout: revoke current token and refresh token
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            if (is_object($user)) {
                // Passport: user()->token() returns the access token model when using token guard
                if (method_exists($user, 'token') && $user->token()) {
                    $token = $user->token();
                    // revoke access token
                    if (method_exists($token, 'revoke')) {
                        $token->revoke();
                    } else {
                        $token->delete();
                    }

                    // Revoke refresh tokens by access token id (best-effort)
                    try {
                        DB::table('oauth_refresh_tokens')->where('access_token_id', $token->id)->update(['revoked' => true]);
                    } catch (\Throwable $e) {
                        // ignore DB failure here (best-effort)
                    }
                }
            }

            // Remove cookies
            $cookieAccess = cookie()->forget('access_token');
            $cookieRefresh = cookie()->forget('refresh_token');

            return $this->successResponse([], 'Déconnexion réussie')
                ->withCookie($cookieAccess)
                ->withCookie($cookieRefresh);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la déconnexion: ' . $e->getMessage(), 500);
        }
    }
}
