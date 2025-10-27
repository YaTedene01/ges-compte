<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client;
use Laravel\Passport\Token;
use Nyholm\Psr7\Factory\Psr17Factory;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $client = Client::where('password_client', 1)->first();

        // Create PSR-7 request
        $psr17Factory = new Psr17Factory();
        $psrRequest = $psr17Factory->createServerRequest('POST', '/oauth/token')
                                   ->withParsedBody([
                                       'grant_type' => 'password',
                                       'client_id' => $client->id,
                                       'client_secret' => $client->secret,
                                       'username' => $user->email,
                                       'password' => $request->password,
                                       'scope' => implode(' ', json_decode($user->scopes ?? '[]', true) ?? []),
                                   ]);

        $response = app(\Laravel\Passport\Http\Controllers\AccessTokenController::class)->issueToken($psrRequest);

        if ($response->getStatusCode() != 200) {
            return response()->json(json_decode($response->getContent(), true), $response->getStatusCode());
        }

        $data = json_decode($response->getContent(), true);

        $cookie = cookie('access_token', $data['access_token'], 60, null, null, false, true);

        return response()->json($data)->withCookie($cookie);
    }

    public function refresh(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token');

        if (!$refreshToken) {
            return response()->json(['error' => 'No refresh token'], 401);
        }

        $token = Token::where('name', 'Personal Access Token')->where('user_id', Auth::id())->first();

        if (!$token) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        $newToken = $token->user->createToken('Personal Access Token', [], false)->accessToken;

        $cookie = cookie('access_token', $newToken, 60, null, null, false, true);

        return response()->json(['access_token' => $newToken, 'token_type' => 'Bearer', 'expires_in' => 3600])
                         ->withCookie($cookie);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        $cookie = cookie('access_token', '', -1);

        return response()->json(['message' => 'Logged out'])->withCookie($cookie);
    }
}