<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
     return $request->user();
});

Route::prefix('v1/faye-yatedene')->group(function () {
        Route::resource('comptes', App\Http\Controllers\Api\V1\CompteController::class)->parameters(['comptes' => 'numero']);
        Route::post('comptes/{numero}/bloquer', [App\Http\Controllers\Api\V1\CompteController::class, 'bloquer']);
        Route::get('comptes/{numero}', [App\Http\Controllers\Api\V1\CompteController::class, 'show']);
});
