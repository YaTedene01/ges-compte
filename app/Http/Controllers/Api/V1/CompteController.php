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
use App\Http\Requests\CompteUpdateRequest;
use App\Exceptions\CompteNotFoundException;
use App\Rules\SenegalesePhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
     * @OA\Get(
     *     path="/v1/faye-yatedene/comptes/{compteId}",
     *     summary="Récupérer un compte spécifique",
     *     description="Récupère les détails d'un compte spécifique par son ID",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         description="ID du compte",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du compte",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Compte")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="COMPTE_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Le compte avec l'ID spécifié n'existe pas"),
     *                 @OA\Property(property="details", type="object")
     *             )
     *         )
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
     *     path="/v1/faye-yatedene/comptes/{numero}",
     *     summary="Mettre à jour un compte",
     *     description="Met à jour les informations du compte et du client associé",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="numero",
     *         in="path",
     *         description="Numéro du compte",
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
     *         description="Compte mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte mis à jour avec succès"),
     *             @OA\Property(property="data", ref="#/components/schemas/Compte")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="COMPTE_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Le compte avec le numéro spécifié n'existe pas")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
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
     *     path="/v1/faye-yatedene/comptes/{numero}",
     *     summary="Archiver un compte",
     *     description="Archive un compte spécifique en changeant son statut à 'ferme' et en supprimant ses transactions",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="numero",
     *         in="path",
     *         description="Numéro du compte",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte archivé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte archivé avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="COMPTE_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Le compte avec le numéro spécifié n'existe pas")
     *             )
     *         )
     *     )
     * )
     */
    public function destroy(string $numero)
    {
        try {
            $compte = Compte::where('numeroCompte', $numero)->firstOrFail();

            // Archive the account
            $compte->update(['statut' => 'ferme']);

            // Archive all transactions (soft delete)
            Transaction::where('compteId', $compte->id)->delete();

            return $this->successResponse(null, 'Compte archivé avec succès');
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de l\'archivage du compte: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Bloquer un compte.
     */
    public function bloquer(CompteBloquerRequest $request, string $numero)
    {
        $compte = Compte::where('numeroCompte', $numero)->firstOrFail();

        $compte->update([
            'statut' => 'bloque',
            'motifBlocage' => $request->motifBlocage,
            'dateDebutBlocage' => $request->dateDebutBlocage,
            'dateFinBlocage' => $request->dateFinBlocage,
        ]);

        return $this->successResponse(new CompteResource($compte), 'Compte bloqué avec succès');
    }
}
