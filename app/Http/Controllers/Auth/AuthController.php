<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client;
use Nyholm\Psr7\Factory\Psr17Factory;

/**
 * Controller pour la gestion de l'authentification
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Authentification utilisateur",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Identifiants invalides"
     *     )
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $client = Client::where('password_client', 1)->first();

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
            
            return response()->json($data);
        }

        return response()->json([
            'message' => 'Identifiants invalides'
        ], 401);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     summary="Inscription d'un nouvel utilisateur",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password"),
     *             @OA\Property(property="password_confirmation", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Utilisateur créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation"
     *     )
     * )
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password'])
        ]);

        $client = Client::where('password_client', 1)->first();

        $psr17Factory = new Psr17Factory();
        $psrRequest = $psr17Factory->createServerRequest('POST', '/oauth/token')
            ->withParsedBody([
                'grant_type' => 'password',
                'client_id' => $client->id,
                'client_secret' => $client->secret,
                'username' => $user->email,
                'password' => $validated['password'],
                'scope' => implode(' ', json_decode($user->scopes ?? '[]', true) ?? []),
            ]);

        $response = app(\Laravel\Passport\Http\Controllers\AccessTokenController::class)->issueToken($psrRequest);

        if ($response->getStatusCode() != 201) {
            return response()->json(json_decode($response->getContent(), true), $response->getStatusCode());
        }

        $data = json_decode($response->getContent(), true);
        $data['user'] = $user;
        
        return response()->json($data, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Déconnexion de l'utilisateur",
     *     tags={"Authentification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié"
     *     )
     * )
     */
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->token()->revoke();
        }
        
        return response()->json([
            'message' => 'Déconnexion réussie'
        ]);
    }
}