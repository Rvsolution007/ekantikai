<?php
// Test Gemini API Integration
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$apiKey = 'AIzaSyCR4jsLH-fOyHbjvV2xUrSDU_V_BWYHPzY';
$model = 'gemini-2.0-flash';

echo "Testing Gemini API...\n";
echo "Model: $model\n\n";

$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

$data = [
    'contents' => [
        [
            'parts' => [
                ['text' => 'Say "Hello from Gemini!" in Hindi']
            ]
        ]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "HTTP Code: $httpCode\n";

if ($httpCode === 200) {
    $result = json_decode($response, true);
    $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'No response';
    echo "✅ Success!\n";
    echo "Response: $text\n";
} else {
    echo "❌ Error!\n";
    echo "Response: $response\n";
}

curl_close($ch);
