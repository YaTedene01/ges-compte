<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Compte;
use App\Http\Resources\CompteResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class CompteController extends Controller
{
    use ApiResponseTrait;

     /**
      * Display a listing of the resource.
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
