<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/', [ApiController::class, 'index']);
Route::get('/me', [ApiController::class, 'me']);
Route::get('/ordens', [ApiController::class, 'orders']);
Route::get('/ordem/{order}', [ApiController::class, 'getOrders']);
Route::get('/autorizacao', [ApiController::class, 'getAuthorization']);
Route::post('/create-user', [ApiController::class, 'createTestUser']);
Route::get('/dados-comprador', [ApiController::class, 'billingData']);
Route::post('/inserir-transportador', [ApiController::class, 'insertTransportador']);
Route::get('/transportadoras', [ApiController::class, 'getTransportadoras']);


// https://api.mercadolibre.com/oauth/token
