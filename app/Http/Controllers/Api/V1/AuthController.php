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

/**
 * @OA\Schema(
 *     schema="LoginRequest",
 *     type="object",
 *     required={"email", "password"},
 *     @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *     @OA\Property(property="password", type="string", example="password123")
 * )
 *
 * @OA\Schema(
 *     schema="AuthResponse",
 *     type="object",
 *     @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
 *     @OA\Property(property="token_type", type="string", example="Bearer"),
 *     @OA\Property(property="expires_in", type="integer", example=3600)
 * )
 */

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/v1/authentication/login",
     *     summary="User login",
     *     description="Authenticates a user and returns an access token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(ref="#/components/schemas/AuthResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="UNAUTHORIZED"),
     *                 @OA\Property(property="message", type="string", example="Invalid credentials"),
     *                 @OA\Property(property="timestamp", type="string", format="date-time"),
     *                 @OA\Property(property="path", type="string"),
     *                 @OA\Property(property="traceId", type="string")
     *             )
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/v1/authentication/refresh",
     *     summary="Refresh access token",
     *     description="Generates a new access token using the refresh token",
     *     tags={"Authentication"},
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully",
     *         @OA\JsonContent(ref="#/components/schemas/AuthResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Missing or invalid refresh token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="UNAUTHORIZED"),
     *                 @OA\Property(property="message", type="string", example="No refresh token"),
     *                 @OA\Property(property="timestamp", type="string", format="date-time"),
     *                 @OA\Property(property="path", type="string"),
     *                 @OA\Property(property="traceId", type="string")
     *             )
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/v1/authentication/logout",
     *     summary="User logout",
     *     description="Revokes the current user's access token",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logged out")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="UNAUTHORIZED"),
     *                 @OA\Property(property="message", type="string", example="Unauthorized"),
     *                 @OA\Property(property="timestamp", type="string", format="date-time"),
     *                 @OA\Property(property="path", type="string"),
     *                 @OA\Property(property="traceId", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        $cookie = cookie('access_token', '', -1);

        return response()->json(['message' => 'Logged out'])->withCookie($cookie);
    }
}