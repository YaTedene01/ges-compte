<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;
use App\Exceptions\CompteNotFoundException;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Disable logging for all exceptions to avoid permission issues
            return false;
        });

        $this->renderable(function (CompteNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'COMPTE_NOT_FOUND',
                        'message' => $e->getMessage(),
                        'details' => [
                            'compteId' => $request->route('compteId') ?? 'unknown'
                        ],
                        'timestamp' => now()->toISOString(),
                        'path' => $request->path(),
                        'traceId' => request()->header('X-Trace-Id', uniqid())
                    ]
                ], 404);
            }
        });

        // Route not found (404)
        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => 'Ressource introuvable',
                        'details' => null,
                        'timestamp' => now()->toISOString(),
                        'path' => $request->path(),
                        'traceId' => request()->header('X-Trace-Id', uniqid())
                    ]
                ], 404);
            }
        });

        // Model not found (Eloquent)
        $this->renderable(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'MODEL_NOT_FOUND',
                        'message' => "Élément demandé introuvable",
                        'details' => null,
                        'timestamp' => now()->toISOString(),
                        'path' => $request->path(),
                        'traceId' => request()->header('X-Trace-Id', uniqid())
                    ]
                ], 404);
            }
        });

        // Validation errors (422)
        $this->renderable(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'Données invalides',
                        'details' => $e->errors(),
                        'timestamp' => now()->toISOString(),
                        'path' => $request->path(),
                        'traceId' => request()->header('X-Trace-Id', uniqid())
                    ]
                ], 422);
            }
        });

        // Authentication (401)
        $this->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHENTICATED',
                        'message' => 'Non authentifié',
                        'details' => null,
                        'timestamp' => now()->toISOString(),
                        'path' => $request->path(),
                        'traceId' => request()->header('X-Trace-Id', uniqid())
                    ]
                ], 401);
            }
        });

        // Rate limiting (429)
        $this->renderable(function (ThrottleRequestsException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'TOO_MANY_REQUESTS',
                        'message' => 'Trop de requêtes',
                        'details' => null,
                        'timestamp' => now()->toISOString(),
                        'path' => $request->path(),
                        'traceId' => request()->header('X-Trace-Id', uniqid())
                    ]
                ], 429);
            }
        });

        // Database query errors (500)
        $this->renderable(function (QueryException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'DB_ERROR',
                        'message' => 'Erreur de base de données',
                        'details' => $e->getMessage(),
                        'timestamp' => now()->toISOString(),
                        'path' => $request->path(),
                        'traceId' => request()->header('X-Trace-Id', uniqid())
                    ]
                ], 500);
            }
        });

        // Fallback for any other exception -> return structured 500
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INTERNAL_ERROR',
                        'message' => 'Erreur interne du serveur',
                        'details' => config('app.debug') ? $e->getMessage() : null,
                        'timestamp' => now()->toISOString(),
                        'path' => $request->path(),
                        'traceId' => request()->header('X-Trace-Id', uniqid())
                    ]
                ], 500);
            }
        });
    }
}
