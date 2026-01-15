<?php

return [
    'ai' => [
        'enabled' => env('AI_ENABLED', true),
        'provider' => env('AI_PROVIDER', 'gemini'),
        'api_key' => env('GEMINI_API_KEY', 'AIzaSyCR4jsLH-fOyHbjvV2xUrSDU_V_BWYHPzY'),
        'model' => env('AI_MODEL', 'gemini-2.0-flash'),
        'vertex_project_id' => env('VERTEX_PROJECT_ID', 'n8n-and-sheet-rv-bot'),
        'vertex_location' => env('VERTEX_LOCATION', 'asia-south1'),
        'vertex_key_path' => env('VERTEX_KEY_PATH'),
        'temperature' => env('AI_TEMPERATURE', 0.3),
        'max_tokens' => env('AI_MAX_TOKENS', 1000),
    ],

    'whatsapp' => [
        'api_url' => env('EVOLUTION_API_URL', 'https://hardware-evolution-api.bwl4v6.easypanel.host'),
        'api_key' => env('EVOLUTION_API_KEY', '429683C4C977415CAAFCCE10F7D57E11'),
        'instance' => env('EVOLUTION_INSTANCE', 'vivo mobile'),
    ],
];
