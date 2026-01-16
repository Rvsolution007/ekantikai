<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration {
    /**
     * Run the migrations.
     * Add global AI configuration settings (Super Admin controlled)
     */
    public function up(): void
    {
        // Add global AI settings to settings table
        $aiSettings = [
            'global_ai_provider' => [
                'value' => 'google',
                'group' => 'ai',
                'type' => 'text',
                'description' => 'Global AI Provider (google, openai, deepseek)'
            ],
            'global_ai_model' => [
                'value' => 'gemini-2.5-flash',
                'group' => 'ai',
                'type' => 'text',
                'description' => 'Global AI Model selected by Super Admin'
            ],
            'openai_api_key' => [
                'value' => '',
                'group' => 'ai',
                'type' => 'encrypted',
                'description' => 'OpenAI API Key'
            ],
            'deepseek_api_key' => [
                'value' => '',
                'group' => 'ai',
                'type' => 'encrypted',
                'description' => 'DeepSeek API Key'
            ],
        ];

        foreach ($aiSettings as $key => $config) {
            if (!Setting::where('key', $key)->exists()) {
                Setting::create([
                    'key' => $key,
                    'value' => $config['type'] === 'encrypted' && $config['value']
                        ? encrypt($config['value'])
                        : $config['value'],
                    'group' => $config['group'],
                    'type' => $config['type'],
                    'description' => $config['description'],
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Setting::whereIn('key', [
            'global_ai_provider',
            'global_ai_model',
            'openai_api_key',
            'deepseek_api_key',
        ])->delete();
    }
};
