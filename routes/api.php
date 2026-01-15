<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// WhatsApp Webhook (Evolution API)
// Main endpoint (uses default instance from settings)
Route::post('/webhook/whatsapp', [WebhookController::class, 'handle'])
    ->defaults('instanceName', 'default')
    ->name('api.webhook.whatsapp');

// Instance-specific endpoint
Route::post('/webhook/whatsapp/{instanceName}', [WebhookController::class, 'handle'])
    ->name('api.webhook.whatsapp.instance');

// Webhook verification (for some providers)
Route::get('/webhook/whatsapp', function (Request $request) {
    return response($request->query('hub_challenge', 'OK'));
});
Route::get('/webhook/whatsapp/{instanceName}', function (Request $request) {
    return response($request->query('hub_challenge', 'OK'));
});
