<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Compte;
use App\Http\Resources\CompteResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

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

class CompteController extends Controller
{
    use ApiResponseTrait;

     /**
      * @OA\Get(
      *     path="/comptes",
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

         return response()->json([
             'success' => true,
             'data' => CompteResource::collection($paginator->items()),
             'pagination' => [
                 'currentPage' => $paginator->currentPage(),
                 'totalPages' => $paginator->lastPage(),
                 'totalItems' => $paginator->total(),
                 'itemsPerPage' => $paginator->perPage(),
                 'hasNext' => $paginator->hasMorePages(),
                 'hasPrevious' => $paginator->currentPage() > 1,
             ],
             'links' => [
                 'self' => $paginator->url($paginator->currentPage()),
                 'next' => $paginator->nextPageUrl(),
                 'first' => $paginator->url(1),
                 'last' => $paginator->url($paginator->lastPage()),
             ],
         ]);
     }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
