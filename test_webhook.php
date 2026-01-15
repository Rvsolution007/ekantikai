<?php
// Test Webhook Endpoint locally

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Simulate Evolution API webhook payload
$webhookPayload = [
    'event' => 'messages.upsert',
    'instance' => 'vivo mobile',
    'data' => [
        'key' => [
            'remoteJid' => '919876543210@s.whatsapp.net',
            'fromMe' => false,
            'id' => 'test_msg_' . time()
        ],
        'pushName' => 'Test Customer',
        'message' => [
            'conversation' => 'Hi, mujhe cabinet handles chahiye'
        ],
        'messageTimestamp' => time()
    ]
];

echo "=== Webhook Test ===\n";
echo "Payload:\n";
echo json_encode($webhookPayload, JSON_PRETTY_PRINT) . "\n\n";

// Make request to webhook
$url = 'http://localhost/Chatbot/datsun-chatbot/public/api/webhook/whatsapp/vivo%20mobile';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookPayload));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "HTTP Code: $httpCode\n";
echo "Response:\n";
echo $response . "\n";

curl_close($ch);
