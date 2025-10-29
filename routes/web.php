<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return response()->json(['message' => 'API is running', 'docs' => 'http://127.0.0.1:3000/api/documentation']);
});

// Swagger documentation routes are provided by the l5-swagger package.
// Do not override the package routes here to avoid serving the UI at the docs endpoint.
// The package registers:
//  - route 'l5-swagger.default.api' (UI) at /api/documentation
//  - route 'l5-swagger.default.docs' (json/yaml docs) at /docs/{file}
// If you need a custom entrypoint, create a redirect to the named route instead of rendering the view.

/**
 * Temporary internal health endpoint.
 * - Enabled only when the ALLOW_INTERNAL_HEALTH env var is truthy (true, "true", 1, etc.).
 * - Returns non-sensitive diagnostics: DB connectivity, migrations table presence, and a small row count.
 * Use this to debug 500s when you don't have shell access on the host (enable, redeploy, then visit /internal/health).
 */
if (filter_var(env('ALLOW_INTERNAL_HEALTH'), FILTER_VALIDATE_BOOLEAN)) {
    Route::get('/internal/health', function () {
        $result = [
            'app' => [
                'env' => env('APP_ENV'),
                'debug' => env('APP_DEBUG'),
            ],
            'db' => [
                'connected' => false,
                'migrations_table' => false,
                'comptes_count' => null,
                'errors' => [],
            ],
        ];

        try {
            // Test DB connection
            DB::connection()->getPdo();
            $result['db']['connected'] = true;

            // Check migrations table
            $result['db']['migrations_table'] = Schema::hasTable('migrations');

            // Return a small count for a relevant table if it exists
            if (Schema::hasTable('comptes')) {
                $result['db']['comptes_count'] = (int) DB::table('comptes')->count();
            }
        } catch (\Throwable $e) {
            $result['db']['errors'][] = substr($e->getMessage(), 0, 500); // truncate to avoid leaking huge traces
        }

        return response()->json($result);
    });
}
