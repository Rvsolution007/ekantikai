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

// Simulate incoming WhatsApp message (for testing webhook processing)
Route::post('/webhook/simulate', function (Request $request) {
    $phone = $request->input('phone', '919876543210');
    $message = $request->input('message', 'Hello Test');
    $instance = $request->input('instance', 'vivo mobile');

    // Create fake Evolution API webhook payload
    $payload = [
        'event' => 'messages.upsert',
        'instance' => $instance,
        'data' => [
            'key' => [
                'remoteJid' => $phone . '@s.whatsapp.net',
                'fromMe' => false,
                'id' => 'TEST_' . time(),
            ],
            'pushName' => 'Test User',
            'message' => [
                'conversation' => $message,
            ],
            'messageTimestamp' => time(),
        ],
    ];

    // Call the actual webhook handler
    $controller = new \App\Http\Controllers\Api\WebhookController();
    $fakeRequest = Request::create('/api/webhook/whatsapp/' . $instance, 'POST', $payload);
    $fakeRequest->headers->set('Content-Type', 'application/json');

    $response = $controller->handle($fakeRequest, $instance);

    return response()->json([
        'status' => 'simulated',
        'payload_sent' => $payload,
        'webhook_response' => json_decode($response->getContent(), true),
    ]);
});

// Check if Evolution API is sending webhooks
Route::get('/webhook/check-evolution', function () {
    $admin = \App\Models\Admin::where('is_active', true)->first();
    if (!$admin) {
        return response()->json(['error' => 'No active admin']);
    }

    $service = new \App\Services\WhatsApp\EvolutionApiService($admin);
    $instance = $admin->whatsapp_instance;

    // Get webhook config from Evolution API
    $webhookConfig = $service->getWebhook($instance);

    return response()->json([
        'instance' => $instance,
        'webhook_config' => $webhookConfig,
        'expected_url' => url('/api/webhook/whatsapp/' . urlencode($instance)),
    ]);
});

// DEBUG: Check all admins and their catalogue data
Route::get('/debug/admin-catalogue', function () {
    $admins = \App\Models\Admin::select('id', 'name', 'company_name', 'whatsapp_instance', 'is_active')->get();

    $result = [];
    foreach ($admins as $admin) {
        $catalogueCount = \App\Models\Catalogue::where('admin_id', $admin->id)->count();
        $activeCatalogueCount = \App\Models\Catalogue::where('admin_id', $admin->id)->where('is_active', true)->count();

        // Get sample products
        $sampleProducts = \App\Models\Catalogue::where('admin_id', $admin->id)
            ->where('is_active', true)
            ->limit(5)
            ->get(['id', 'product_type', 'model_code', 'category']);

        $result[] = [
            'admin_id' => $admin->id,
            'name' => $admin->name,
            'company_name' => $admin->company_name,
            'whatsapp_instance' => $admin->whatsapp_instance,
            'is_active' => $admin->is_active,
            'total_catalogue' => $catalogueCount,
            'active_catalogue' => $activeCatalogueCount,
            'sample_products' => $sampleProducts,
        ];
    }

    return response()->json([
        'total_admins' => count($admins),
        'admins' => $result,
    ]);
});
