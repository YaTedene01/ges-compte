<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Compte;
use App\Models\Client;
use App\Http\Resources\CompteResource;
use App\Traits\ApiResponseTrait;
use App\Http\Requests\CompteCreationRequest;
use Illuminate\Http\Request;

class CompteController extends Controller
{
     use ApiResponseTrait;

     /**
      * @OA\Info(
      *     title="API de Gestion de Comptes",
      *     version="1.0.0",
      *     description="API pour la gestion des comptes bancaires"
      * )
      *
      * @OA\Schema(
      *     schema="Compte",
      *     type="object",
      *     @OA\Property(property="id", type="string"),
      *     @OA\Property(property="numeroCompte", type="string"),
      *     @OA\Property(property="titulaire", type="string"),
      *     @OA\Property(property="type", type="string"),
      *     @OA\Property(property="solde", type="number"),
      *     @OA\Property(property="devise", type="string"),
      *     @OA\Property(property="dateCreation", type="string", format="date"),
      *     @OA\Property(property="statut", type="string"),
      *     @OA\Property(property="motifBlocage", type="string"),
      *     @OA\Property(property="metadata", type="object")
      * )
      */

      /**
      * @OA\Get(
      *     path="/v1/faye-yatedene/comptes",
      *     summary="Liste des comptes",
      *     description="Récupère la liste des comptes avec filtres et pagination",
      *     tags={"Comptes"},
      *     @OA\Parameter(
      *         name="type",
      *         in="query",
      *         description="Type de compte",
      *         required=false,
      *         @OA\Schema(type="string")
      *     ),
      *     @OA\Parameter(
      *         name="statut",
      *         in="query",
      *         description="Statut du compte",
      *         required=false,
      *         @OA\Schema(type="string")
      *     ),
      *     @OA\Parameter(
      *         name="search",
      *         in="query",
      *         description="Recherche par titulaire ou numéro",
      *         required=false,
      *         @OA\Schema(type="string")
      *     ),
      *     @OA\Parameter(
      *         name="sort",
      *         in="query",
      *         description="Champ de tri",
      *         required=false,
      *         @OA\Schema(type="string", default="dateCreation")
      *     ),
      *     @OA\Parameter(
      *         name="order",
      *         in="query",
      *         description="Ordre de tri",
      *         required=false,
      *         @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")
      *     ),
      *     @OA\Parameter(
      *         name="limit",
      *         in="query",
      *         description="Nombre d'éléments par page",
      *         required=false,
      *         @OA\Schema(type="integer", default=10, maximum=100)
      *     ),
      *     @OA\Response(
      *         response=200,
      *         description="Liste des comptes",
      *         @OA\JsonContent(
      *             @OA\Property(property="success", type="boolean", example=true),
      *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Compte")),
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
     *     path="/v1/faye-yatedene/comptes",
     *     summary="Créer un nouveau compte",
     *     description="Crée un nouveau compte bancaire avec vérification du client",
     *     tags={"Comptes"},
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
     *                 @OA\Property(property="adresse", type="string", example="Dakar, Sénégal")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte créé avec succès"),
     *             @OA\Property(property="data", ref="#/components/schemas/Compte")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Données invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Les données fournies sont invalides"),
     *                 @OA\Property(property="details", type="object")
     *             )
     *         )
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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
