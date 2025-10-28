<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Compte;
use App\Models\Client;
use App\Models\Transaction;
use App\Http\Resources\CompteResource;
use App\Traits\ApiResponseTrait;
use App\Http\Requests\CompteCreationRequest;
use App\Http\Requests\CompteBloquerRequest;
use App\Http\Requests\CompteDebloquerRequest;
use App\Http\Requests\CompteUpdateRequest;
use App\Exceptions\CompteNotFoundException;
use App\Rules\SenegalesePhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * @OA\Info(
 *     title="Bank Account Management API",
 *     version="1.0.0",
 *     description="API for managing bank accounts",
 *     @OA\Contact(
 *         name="Faye Yatedene",
 *         email="faye.yatedene@example.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url="https://ges-compte.onrender.com/api/v1",
 *     description="Production server"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api/v1",
 *     description="Development server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="JWT Authorization header using the Bearer scheme. Example: 'Authorization: Bearer {token}'"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Accounts",
 *     description="Bank account management endpoints"
 * )
 *
 * @OA\ExternalDocumentation(
 *     description="API Documentation",
 *     url="https://example.com/docs"
 * )
 *
 * @OA\Schema(
 *     schema="Account",
 *     type="object",
 *     @OA\Property(property="id", type="string", example="uuid-string"),
 *     @OA\Property(property="numeroCompte", type="string", example="ACC001234567"),
 *     @OA\Property(property="titulaire", type="string", example="John Doe"),
 *     @OA\Property(property="type", type="string", enum={"cheque", "epargne"}, example="cheque"),
 *     @OA\Property(property="solde", type="number", example=500000),
 *     @OA\Property(property="devise", type="string", example="FCFA"),
 *     @OA\Property(property="dateCreation", type="string", format="date", example="2025-01-15"),
 *     @OA\Property(property="statut", type="string", enum={"actif", "bloque", "ferme"}, example="actif"),
 *     @OA\Property(property="motifBlocage", type="string", nullable=true, example="Suspicious activity"),
 *     @OA\Property(property="dateFermeture", type="string", format="date-time", nullable=true, example="2025-10-27T18:00:00Z"),
 *     @OA\Property(property="metadata", type="object", example={"derniereModification": "2025-10-27T18:00:00Z", "version": 1})
 * )
 *
 *
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="error", type="object",
 *         @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
 *         @OA\Property(property="message", type="string", example="Validation failed"),
 *         @OA\Property(property="details", type="object", example={"field": "error message"}),
 *         @OA\Property(property="timestamp", type="string", format="date-time", example="2025-10-27T18:00:00Z"),
 *         @OA\Property(property="path", type="string", example="/api/v1/accounts"),
 *         @OA\Property(property="traceId", type="string", example="abc-def-ghi-123")
 *     )
 * )
 *
 * @OA\Parameter(
 *     parameter="AuthorizationHeader",
 *     name="Authorization",
 *     in="header",
 *     required=true,
 *     description="JWT access token",
 *     @OA\Schema(type="string", example="Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...")
 * )
 *
 * @OA\Parameter(
 *     parameter="ContentTypeHeader",
 *     name="Content-Type",
 *     in="header",
 *     required=true,
 *     description="Content type",
 *     @OA\Schema(type="string", example="application/json")
 * )
 *
 * @OA\Parameter(
 *     parameter="AcceptHeader",
 *     name="Accept",
 *     in="header",
 *     required=true,
 *     description="Accept header",
 *     @OA\Schema(type="string", example="application/json")
 * )
 *
 * @OA\Parameter(
 *     parameter="AcceptLanguageHeader",
 *     name="Accept-Language",
 *     in="header",
 *     required=false,
 *     description="Language preference",
 *     @OA\Schema(type="string", example="fr-FR")
 * )
 *
 * @OA\Parameter(
 *     parameter="RequestIdHeader",
 *     name="X-Request-ID",
 *     in="header",
 *     required=false,
 *     description="Unique request identifier",
 *     @OA\Schema(type="string", example="unique-request-id")
 * )
 *
 * @OA\Parameter(
 *     parameter="ApiVersionHeader",
 *     name="X-API-Version",
 *     in="header",
 *     required=false,
 *     description="API version",
 *     @OA\Schema(type="string", example="v1")
 * )
 */

class CompteController extends Controller
{
      use ApiResponseTrait;

      /**
      * @OA\Get(
      *     path="/v1/accounts",
      *     summary="List accounts",
      *     description="Retrieve a list of accounts with filters and pagination",
      *     tags={"Accounts"},
      *     security={{"bearerAuth": {}}},
      *     @OA\Parameter(
      *         name="type",
      *         in="query",
      *         description="Account type",
      *         required=false,
      *         @OA\Schema(type="string")
      *     ),
      *     @OA\Parameter(
      *         name="statut",
      *         in="query",
      *         description="Account status",
      *         required=false,
      *         @OA\Schema(type="string")
      *     ),
      *     @OA\Parameter(
      *         name="search",
      *         in="query",
      *         description="Search by holder name or account number",
      *         required=false,
      *         @OA\Schema(type="string")
      *     ),
      *     @OA\Parameter(
      *         name="sort",
      *         in="query",
      *         description="Sort field",
      *         required=false,
      *         @OA\Schema(type="string", default="dateCreation")
      *     ),
      *     @OA\Parameter(
      *         name="order",
      *         in="query",
      *         description="Sort order",
      *         required=false,
      *         @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")
      *     ),
      *     @OA\Parameter(
      *         name="limit",
      *         in="query",
      *         description="Items per page",
      *         required=false,
      *         @OA\Schema(type="integer", default=10, maximum=100)
      *     ),
      *     @OA\Response(
      *         response=200,
      *         description="List of accounts",
      *         @OA\JsonContent(
      *             @OA\Property(property="success", type="boolean", example=true),
      *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Account")),
      *             @OA\Property(property="pagination", type="object",
      *                 @OA\Property(property="currentPage", type="integer"),
      *                 @OA\Property(property="totalPages", type="integer"),
      *                 @OA\Property(property="totalItems", type="integer"),
      *                 @OA\Property(property="itemsPerPage", type="integer"),
      *                 @OA\Property(property="hasNext", type="boolean"),
      *                 @OA\Property(property="hasPrevious", type="boolean")
      *             ),
      *             @OA\Property(property="links", type="object")
      *         )
      *     ),
      *     @OA\Response(
      *         response=401,
      *         description="Unauthorized",
      *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
      *     ),
      *     @OA\Response(
      *         response=429,
      *         description="Too many requests",
      *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
      *     )
      * )
      */
     public function index(Request $request)
     {
         $query = Compte::with('client');

         // Filters
         if ($request->has('type')) {
             $query->where('type', $request->type);
         }

         if ($request->has('statut')) {
             $query->where('statut', $request->statut);
         }

         if ($request->has('search')) {
             $search = $request->search;
             $query->where('titulaire', 'like', "%{$search}%")
                   ->orWhere('numeroCompte', 'like', "%{$search}%");
         }

         // Sorting
         $sort = $request->get('sort', 'dateCreation');
         $order = $request->get('order', 'desc');
         $query->orderBy($sort, $order);

         // Pagination
         $limit = min($request->get('limit', 10), 100);
         $paginator = $query->paginate($limit)->appends($request->query());

         return $this->paginatedResponse($paginator, 'Liste des comptes récupérée avec succès');
     }

    /**
     * @OA\Post(
     *     path="/v1/accounts",
     *     summary="Create a new account",
     *     description="Create a new bank account with client verification",
     *     tags={"Accounts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type", "soldeInitial", "devise", "client"},
     *             @OA\Property(property="type", type="string", enum={"cheque", "epargne"}, example="cheque"),
     *             @OA\Property(property="soldeInitial", type="number", minimum=10000, example=500000),
     *             @OA\Property(property="devise", type="string", example="FCFA"),
     *             @OA\Property(property="client", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", nullable=true),
     *                 @OA\Property(property="titulaire", type="string", example="Hawa BB Wane"),
     *                 @OA\Property(property="nci", type="string", example="1234567890123", nullable=true),
     *                 @OA\Property(property="email", type="string", format="email", example="cheikh.sy@example.com"),
     *                 @OA\Property(property="telephone", type="string", example="+221771234567"),
     *                 @OA\Property(property="adresse", type="string", example="Dakar, Senegal")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Account created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Account created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Account")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid data",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many requests",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function store(CompteCreationRequest $request)
    {
        $validated = $request->validated();

        // Check if client exists
        $client = Client::where('email', $validated['client']['email'])->first();

        if (!$client) {
            // Create new client
            $client = Client::create([
                'id' => $validated['client']['id'] ?? null,
                'titulaire' => $validated['client']['titulaire'],
                'nci' => $validated['client']['nci'] ?? null,
                'email' => $validated['client']['email'],
                'telephone' => $validated['client']['telephone'],
                'adresse' => $validated['client']['adresse'],
            ]);
        }

        // Create account
        $compte = Compte::create([
            'id' => $validated['client']['id'] ?? null,
            'titulaire' => $client->titulaire,
            'type' => $validated['type'],
            'devise' => $validated['devise'],
            'dateCreation' => now(),
            'statut' => 'actif',
            'motifBlocage' => null,
            'metadata' => [
                'derniereModification' => now(),
                'version' => 1,
            ],
            'client_id' => $client->id,
        ]);

        return $this->successResponse(new CompteResource($compte), 'Compte créé avec succès', 201);
    }

    /**
     * @OA\Get(
     *     path="/v1/accounts/{numero}",
     *     summary="Get specific account",
     *     description="Retrieve details of a specific account by its number",
     *     tags={"Accounts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="numero",
     *         in="path",
     *         description="Account number",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Account")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Account not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many requests",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show(string $compteId)
    {
        $compte = Compte::where('numeroCompte', $compteId)->first();

        if (!$compte) {
            throw new CompteNotFoundException($compteId);
        }

        return $this->successResponse(new CompteResource($compte), 'Compte récupéré avec succès');
    }

    /**
     * @OA\Patch(
     *     path="/v1/accounts/{numero}",
     *     summary="Update account",
     *     description="Update account and associated client information",
     *     tags={"Accounts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="numero",
     *         in="path",
     *         description="Account number",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="titulaire", type="string", example="Amadou Diallo Junior"),
     *             @OA\Property(property="informationsClient", type="object",
     *                 @OA\Property(property="telephone", type="string", example="+221771234568"),
     *                 @OA\Property(property="email", type="string", format="email", example="amadou.diallo@example.com"),
     *                 @OA\Property(property="password", type="string", example="newpassword123"),
     *                 @OA\Property(property="nci", type="string", example="1234567890123")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Account updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Account")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Account not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many requests",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function update(CompteUpdateRequest $request, string $numero)
    {
        $compte = Compte::where('numeroCompte', $numero)->firstOrFail();
        $client = $compte->client;

        $validated = $request->validated();

        // Check if at least one field is provided
        $hasTitulaire = isset($validated['titulaire']);
        $hasClientInfo = isset($validated['informationsClient']) && is_array($validated['informationsClient']) &&
                         (isset($validated['informationsClient']['telephone']) ||
                          isset($validated['informationsClient']['email']) ||
                          isset($validated['informationsClient']['password']) ||
                          isset($validated['informationsClient']['nci']));

        if (!$hasTitulaire && !$hasClientInfo) {
            return $this->errorResponse('Au moins un champ de modification doit être fourni.', 422);
        }

        // Validate unique constraints manually
        if (isset($validated['informationsClient']['telephone'])) {
            $existingClient = Client::where('telephone', $validated['informationsClient']['telephone'])
                                   ->where('id', '!=', $client->id)->first();
            if ($existingClient) {
                return $this->errorResponse('Ce numéro de téléphone est déjà utilisé.', 422);
            }
        }

        if (isset($validated['informationsClient']['email'])) {
            $existingClient = Client::where('email', $validated['informationsClient']['email'])
                                   ->where('id', '!=', $client->id)->first();
            if ($existingClient) {
                return $this->errorResponse('Cette adresse email est déjà utilisée.', 422);
            }
        }

        if (isset($validated['informationsClient']['nci'])) {
            $existingClient = Client::where('nci', $validated['informationsClient']['nci'])
                                   ->where('id', '!=', $client->id)->first();
            if ($existingClient) {
                return $this->errorResponse('Ce numéro NCI est déjà utilisé.', 422);
            }
        }

        // Validate Senegalese phone number
        if (isset($validated['informationsClient']['telephone'])) {
            $phoneRule = new SenegalesePhoneNumber();
            $phoneRule->validate('telephone', $validated['informationsClient']['telephone'], function ($message) {
                throw new \Exception($message);
            });
        }

        // Update Compte
        if (isset($validated['titulaire'])) {
            $compte->update(['titulaire' => $validated['titulaire']]);
        }

        // Update Client
        if (isset($validated['informationsClient'])) {
            $clientData = [];

            if (isset($validated['informationsClient']['telephone'])) {
                $clientData['telephone'] = $validated['informationsClient']['telephone'];
            }

            if (isset($validated['informationsClient']['email'])) {
                $clientData['email'] = $validated['informationsClient']['email'];
            }

            if (isset($validated['informationsClient']['password'])) {
                $clientData['password'] = Hash::make($validated['informationsClient']['password']);
            }

            if (isset($validated['informationsClient']['nci'])) {
                $clientData['nci'] = $validated['informationsClient']['nci'];
            }

            if (!empty($clientData)) {
                $client->update($clientData);
            }
        }

        // Update metadata
        $compte->update([
            'metadata' => array_merge($compte->metadata ?? [], [
                'derniereModification' => now(),
                'version' => ($compte->metadata['version'] ?? 1) + 1,
            ])
        ]);

        return $this->successResponse(new CompteResource($compte->fresh()), 'Compte mis à jour avec succès');
    }

    /**
     * @OA\Delete(
     *     path="/v1/accounts/{numero}",
     *     summary="Delete account",
     *     description="Soft delete an account, change status to 'ferme' and delete associated transactions",
     *     tags={"Accounts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="numero",
     *         in="path",
     *         description="Account number",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Account deleted successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Account")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Account not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many requests",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function destroy(string $numero)
    {
        try {
            $compte = Compte::where('numeroCompte', $numero)->firstOrFail();

            // Update status and dateFermeture before soft delete
            $compte->update([
                'statut' => 'ferme',
                'dateFermeture' => now()
            ]);

            // Soft delete all transactions
            Transaction::where('compteId', $compte->id)->delete();

            // Soft delete the account
            $compte->delete();

            // Return the account data before deletion for the response
            return $this->successResponse(new CompteResource($compte), 'Compte supprimé avec succès');
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la suppression du compte: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/v1/accounts/{numero}/block",
     *     summary="Block account",
     *     description="Block an active account and calculate blocking dates",
     *     tags={"Accounts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="numero",
     *         in="path",
     *         description="Account number",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"motif", "duree", "unite"},
     *             @OA\Property(property="motif", type="string", example="Suspicious activity detected"),
     *             @OA\Property(property="duree", type="integer", minimum=1, example=30),
     *             @OA\Property(property="unite", type="string", enum={"jours", "mois", "annees"}, example="mois")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account blocked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Account blocked successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="statut", type="string", example="bloque"),
     *                 @OA\Property(property="motifBlocage", type="string"),
     *                 @OA\Property(property="dateBlocage", type="string", format="date-time"),
     *                 @OA\Property(property="dateDeblocagePrevue", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Account not active or invalid data",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Account not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many requests",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function bloquer(CompteBloquerRequest $request, string $numero)
    {
        $compte = Compte::where('numeroCompte', $numero)->firstOrFail();

        if ($compte->statut !== 'actif') {
            return $this->errorResponse('Le compte doit être actif pour être bloqué.', 400);
        }

        $dateDebut = now();
        $dateFin = match ($request->unite) {
            'jours' => $dateDebut->copy()->addDays($request->duree),
            'mois' => $dateDebut->copy()->addMonths($request->duree),
            'annees' => $dateDebut->copy()->addYears($request->duree),
        };

        $compte->update([
            'statut' => 'bloque',
            'motifBlocage' => $request->motif,
            'dateDebutBlocage' => $dateDebut,
            'dateFinBlocage' => $dateFin,
        ]);

        return $this->successResponse([
            'id' => $compte->id,
            'statut' => $compte->statut,
            'motifBlocage' => $compte->motifBlocage,
            'dateBlocage' => $compte->dateDebutBlocage,
            'dateDeblocagePrevue' => $compte->dateFinBlocage,
        ], 'Compte bloqué avec succès');
    }

    /**
     * @OA\Patch(
     *     path="/v1/accounts/{numero}/unblock",
     *     summary="Unblock account",
     *     description="Unblock a blocked account",
     *     tags={"Accounts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="numero",
     *         in="path",
     *         description="Account number",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"motif"},
     *             @OA\Property(property="motif", type="string", example="Verification completed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account unblocked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Account unblocked successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="statut", type="string", example="actif"),
     *                 @OA\Property(property="dateDeblocage", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Account not blocked",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Account not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many requests",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function debloquer(CompteDebloquerRequest $request, string $numero)
    {
        $compte = Compte::where('numeroCompte', $numero)->firstOrFail();

        if ($compte->statut !== 'bloque') {
            return $this->errorResponse('Le compte n\'est pas bloqué.', 400);
        }

        $compte->update([
            'statut' => 'actif',
            'motifBlocage' => null,
            'dateDebutBlocage' => null,
            'dateFinBlocage' => null,
        ]);

        return $this->successResponse([
            'id' => $compte->id,
            'statut' => $compte->statut,
            'dateDeblocage' => now(),
        ], 'Compte débloqué avec succès');
    }
}
