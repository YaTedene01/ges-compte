<?php

use Illuminate\Support\Facades\Route;

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
