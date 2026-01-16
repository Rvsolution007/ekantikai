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

// Webhook test/ping endpoint - to verify webhook is accessible
Route::any('/webhook/test', function (Request $request) {
    $logEntry = date('Y-m-d H:i:s') . " | TEST PING | Method: " . $request->method() . " | IP: " . $request->ip() . "\n";
    file_put_contents(storage_path('logs/webhook_debug.log'), $logEntry, FILE_APPEND);

    return response()->json([
        'status' => 'ok',
        'message' => 'Webhook endpoint is accessible',
        'timestamp' => now()->toIso8601String(),
        'ip' => $request->ip(),
        'method' => $request->method(),
    ]);
});
