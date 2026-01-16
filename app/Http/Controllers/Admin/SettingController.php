<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\WhatsApp\EvolutionApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    /**
     * Display settings page
     */
    public function index()
    {
        $admin = auth('admin')->user();

        // Get WhatsApp settings from Admin model (per-admin)
        $settings = [
            'whatsapp_api_url' => $admin->whatsapp_api_url ?? '',
            'whatsapp_api_key' => $admin->whatsapp_api_key ?? '',
            'whatsapp_instance' => $admin->whatsapp_instance ?? '',
            // Bot Control
            'bot_control_number' => $admin->bot_control_number ?? '',
            // Lead settings
            'lead_timeout_hours' => $admin->lead_timeout_hours ?? 24,
            // Followup settings
            'followup_delay_minutes' => Setting::getValue('followup_delay_minutes', 60),
            // AI settings (global for now)
            'ai_system_prompt' => Setting::getValue('ai_system_prompt', ''),
            'ai_tone' => Setting::getValue('ai_tone', 'friendly'),
            'ai_max_length' => Setting::getValue('ai_max_length', 'medium'),
            // Business settings
            'business_name' => $admin->company_name ?? '',
            'business_email' => $admin->email ?? '',
            'business_hours' => Setting::getValue('business_hours', ''),
        ];

        // Check WhatsApp connection status
        $whatsappConnected = false;
        try {
            if (!empty($settings['whatsapp_api_url']) && !empty($settings['whatsapp_instance'])) {
                $evolutionApi = new EvolutionApiService($admin);
                $result = $evolutionApi->checkConnection($settings['whatsapp_instance']);
                $state = $result['instance']['state'] ?? $result['state'] ?? 'unknown';
                $whatsappConnected = $state === 'open';
            }
        } catch (\Exception $e) {
            // Ignore connection errors on page load
        }

        return view('admin.settings.index', compact('settings', 'whatsappConnected'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $admin = auth('admin')->user();

        // Update WhatsApp settings on Admin model (per-admin)
        $admin->update([
            'whatsapp_api_url' => $request->input('whatsapp_api_url'),
            'whatsapp_api_key' => $request->input('whatsapp_api_key'),
            'whatsapp_instance' => $request->input('whatsapp_instance'),
            'bot_control_number' => $request->input('bot_control_number'),
            'company_name' => $request->input('business_name'),
            'lead_timeout_hours' => (int) $request->input('lead_timeout_hours', 24),
        ]);

        // Other settings can remain global
        $globalSettings = ['ai_system_prompt', 'business_hours', 'ai_tone', 'ai_max_length', 'followup_delay_minutes'];
        foreach ($globalSettings as $key) {
            $value = $request->input($key);
            if ($value !== null) {
                Setting::setValue($key, $value);
            }
        }

        return back()->with('success', 'Settings saved successfully!');
    }

    /**
     * Test WhatsApp connection
     */
    public function testConnection()
    {
        try {
            $admin = auth('admin')->user();
            $instance = $admin->whatsapp_instance ?? '';

            if (empty($instance)) {
                return response()->json([
                    'connected' => false,
                    'message' => 'Instance name is not configured'
                ]);
            }

            $evolutionApi = new EvolutionApiService($admin);
            $result = $evolutionApi->checkConnection($instance);

            // Handle nested response structure from Evolution API
            $state = $result['instance']['state'] ?? $result['state'] ?? 'unknown';
            $connected = $state === 'open';

            // Update admin's whatsapp_connected status
            $admin->update(['whatsapp_connected' => $connected]);

            return response()->json([
                'connected' => $connected,
                'state' => $state,
                'message' => $connected ? 'WhatsApp is connected!' : 'WhatsApp is not connected. Please scan QR code.'
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp connection test failed', ['error' => $e->getMessage()]);

            return response()->json([
                'connected' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get QR Code for WhatsApp connection
     */
    public function getQrCode()
    {
        try {
            $admin = auth('admin')->user();
            $instance = $admin->whatsapp_instance ?? '';

            if (empty($instance)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Instance name is not configured'
                ]);
            }

            $evolutionApi = new EvolutionApiService($admin);

            // First check if already connected
            $connectionState = $evolutionApi->checkConnection($instance);
            $state = $connectionState['instance']['state'] ?? $connectionState['state'] ?? 'unknown';

            if ($state === 'open') {
                return response()->json([
                    'connected' => true,
                    'message' => 'WhatsApp is already connected! No QR code needed.'
                ]);
            }

            // Get QR code
            $result = $evolutionApi->getQrCode($instance);

            Log::info('QR Code response', ['result' => $result]);

            // Handle various response formats from Evolution API
            if (isset($result['base64'])) {
                return response()->json([
                    'success' => true,
                    'qrcode' => 'data:image/png;base64,' . $result['base64']
                ]);
            } elseif (isset($result['code'])) {
                return response()->json([
                    'success' => true,
                    'qrcode' => 'data:image/png;base64,' . $result['code']
                ]);
            } elseif (isset($result['qrcode']['base64'])) {
                return response()->json([
                    'success' => true,
                    'qrcode' => 'data:image/png;base64,' . $result['qrcode']['base64']
                ]);
            } elseif (isset($result['pairingCode'])) {
                // Some versions return pairing code instead
                return response()->json([
                    'success' => true,
                    'pairingCode' => $result['pairingCode'],
                    'message' => 'Use this pairing code: ' . $result['pairingCode']
                ]);
            }

            // Debug: return what we got
            return response()->json([
                'success' => false,
                'message' => 'Could not get QR code. Instance may already be connected or not exist.',
                'debug' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('QR Code fetch failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Disconnect WhatsApp instance
     */
    public function disconnect()
    {
        try {
            $admin = auth('admin')->user();
            $instance = $admin->whatsapp_instance ?? '';

            if (empty($instance)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Instance name is not configured'
                ]);
            }

            $evolutionApi = new EvolutionApiService($admin);
            $result = $evolutionApi->logout($instance);

            // Update admin's whatsapp_connected status
            $admin->update(['whatsapp_connected' => false]);

            return response()->json([
                'success' => true,
                'message' => 'WhatsApp disconnected successfully! You can now scan a new QR code.'
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp disconnect failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Disconnect error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Diagnose WhatsApp and Bot Configuration
     */
    public function diagnoseWhatsApp()
    {
        $admin = auth('admin')->user();
        $checks = [];
        $errors = [];
        $warnings = [];

        // 1. Check Evolution API Configuration
        $checks['evolution_api'] = [
            'name' => 'Evolution API URL',
            'status' => !empty($admin->whatsapp_api_url) ? 'ok' : 'error',
            'message' => !empty($admin->whatsapp_api_url)
                ? 'Configured: ' . $admin->whatsapp_api_url
                : 'Evolution API URL not configured',
        ];

        $checks['api_key'] = [
            'name' => 'API Key',
            'status' => !empty($admin->whatsapp_api_key) ? 'ok' : 'error',
            'message' => !empty($admin->whatsapp_api_key)
                ? 'API Key is set'
                : 'API Key not configured',
        ];

        $checks['instance'] = [
            'name' => 'Instance Name',
            'status' => !empty($admin->whatsapp_instance) ? 'ok' : 'error',
            'message' => !empty($admin->whatsapp_instance)
                ? 'Instance: ' . $admin->whatsapp_instance
                : 'Instance name not configured',
        ];

        // 2. Check WhatsApp Connection Status
        $connectionStatus = 'unknown';
        try {
            if (!empty($admin->whatsapp_api_url) && !empty($admin->whatsapp_instance)) {
                $evolutionApi = new EvolutionApiService($admin);
                $result = $evolutionApi->checkConnection($admin->whatsapp_instance);
                $state = $result['instance']['state'] ?? $result['state'] ?? 'unknown';
                $connectionStatus = $state;

                $checks['connection'] = [
                    'name' => 'WhatsApp Connection',
                    'status' => $state === 'open' ? 'ok' : 'error',
                    'message' => $state === 'open'
                        ? 'WhatsApp is connected'
                        : 'WhatsApp not connected. Status: ' . $state . '. Please scan QR code.',
                ];
            } else {
                $checks['connection'] = [
                    'name' => 'WhatsApp Connection',
                    'status' => 'error',
                    'message' => 'Cannot check connection - API not configured',
                ];
            }
        } catch (\Exception $e) {
            $checks['connection'] = [
                'name' => 'WhatsApp Connection',
                'status' => 'error',
                'message' => 'Connection check failed: ' . $e->getMessage(),
            ];
        }

        // 3. Check Webhook Configuration
        try {
            if (!empty($admin->whatsapp_api_url) && !empty($admin->whatsapp_instance)) {
                $evolutionApi = new EvolutionApiService($admin);
                $webhookInfo = $evolutionApi->getWebhook($admin->whatsapp_instance);

                $webhookUrl = $webhookInfo['webhook']['url'] ?? $webhookInfo['url'] ?? null;
                $webhookEnabled = $webhookInfo['webhook']['enabled'] ?? $webhookInfo['enabled'] ?? false;

                if ($webhookUrl && $webhookEnabled) {
                    $checks['webhook'] = [
                        'name' => 'Webhook',
                        'status' => 'ok',
                        'message' => 'Webhook registered: ' . $webhookUrl,
                    ];
                } else {
                    $checks['webhook'] = [
                        'name' => 'Webhook',
                        'status' => 'error',
                        'message' => 'Webhook not registered or disabled. Messages won\'t be received.',
                        'fix' => 'Register webhook in Evolution API settings',
                    ];
                }
            }
        } catch (\Exception $e) {
            $checks['webhook'] = [
                'name' => 'Webhook',
                'status' => 'warning',
                'message' => 'Could not check webhook: ' . $e->getMessage(),
            ];
        }

        // 4. Check AI Configuration
        $geminiKey = Setting::getValue('gemini_api_key', '') ?: $admin->gemini_api_key;
        $checks['ai_key'] = [
            'name' => 'AI API Key (Gemini)',
            'status' => !empty($geminiKey) ? 'ok' : 'error',
            'message' => !empty($geminiKey)
                ? 'Gemini API key is configured'
                : 'Gemini API key not configured. Bot cannot generate responses.',
        ];

        // 5. Check Workflow/Questionnaire Fields
        $fieldsCount = \App\Models\QuestionnaireField::where('admin_id', $admin->id)
            ->where('is_active', true)
            ->count();

        $checks['workflow_fields'] = [
            'name' => 'Workflow Fields',
            'status' => $fieldsCount > 0 ? 'ok' : 'warning',
            'message' => $fieldsCount > 0
                ? $fieldsCount . ' active fields configured'
                : 'No workflow fields configured. Bot may not ask questions.',
        ];

        // 6. Check Flowchart Nodes
        $nodesCount = \App\Models\QuestionnaireNode::where('admin_id', $admin->id)
            ->where('is_active', true)
            ->count();

        $checks['flowchart'] = [
            'name' => 'Flowchart',
            'status' => $nodesCount > 0 ? 'ok' : 'warning',
            'message' => $nodesCount > 0
                ? $nodesCount . ' active nodes in flowchart'
                : 'No flowchart nodes configured.',
        ];

        // 7. Check Recent Messages
        $lastMessage = \App\Models\WhatsappChat::where('admin_id', $admin->id)
            ->where('role', 'user')
            ->latest()
            ->first();

        if ($lastMessage) {
            $checks['last_message'] = [
                'name' => 'Last Incoming Message',
                'status' => 'info',
                'message' => 'Last message received: ' . $lastMessage->created_at->diffForHumans() . ' from ' . ($lastMessage->number ?? 'unknown'),
            ];
        } else {
            $checks['last_message'] = [
                'name' => 'Last Incoming Message',
                'status' => 'warning',
                'message' => 'No incoming messages found. Check if webhook is working.',
            ];
        }

        // 8. Check Recent Bot Responses
        $lastBotResponse = \App\Models\WhatsappChat::where('admin_id', $admin->id)
            ->where('role', 'bot')
            ->latest()
            ->first();

        if ($lastBotResponse) {
            $checks['last_response'] = [
                'name' => 'Last Bot Response',
                'status' => 'info',
                'message' => 'Last bot response: ' . $lastBotResponse->created_at->diffForHumans(),
            ];
        } else {
            $checks['last_response'] = [
                'name' => 'Last Bot Response',
                'status' => 'warning',
                'message' => 'No bot responses found in history.',
            ];
        }

        // 9. Check Laravel Error Logs
        $logPath = storage_path('logs/laravel.log');
        $recentErrors = [];

        if (file_exists($logPath)) {
            $logContent = file_get_contents($logPath);
            // Get last 10KB
            $logContent = substr($logContent, -10240);

            // Find webhook or WhatsApp related errors
            if (preg_match_all('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*?(ERROR|error).*?(webhook|whatsapp|evolution|gemini|ai)/i', $logContent, $matches)) {
                $recentErrors = array_slice($matches[0], -3);
            }
        }

        $checks['error_log'] = [
            'name' => 'Recent Errors',
            'status' => empty($recentErrors) ? 'ok' : 'warning',
            'message' => empty($recentErrors)
                ? 'No recent WhatsApp/AI errors in logs'
                : 'Found ' . count($recentErrors) . ' recent errors',
            'details' => $recentErrors,
        ];

        // 10. Check Bot Control Number
        $checks['bot_control'] = [
            'name' => 'Bot Control Number',
            'status' => !empty($admin->bot_control_number) ? 'ok' : 'info',
            'message' => !empty($admin->bot_control_number)
                ? 'Control number: ' . $admin->bot_control_number
                : 'Not configured (optional)',
        ];

        // Summary
        $errorCount = count(array_filter($checks, fn($c) => $c['status'] === 'error'));
        $warningCount = count(array_filter($checks, fn($c) => $c['status'] === 'warning'));
        $okCount = count(array_filter($checks, fn($c) => $c['status'] === 'ok'));

        // Generate fix suggestions
        $fixes = [];
        foreach ($checks as $key => $check) {
            if ($check['status'] === 'error') {
                $fixes[] = $this->getSuggestedFix($key, $check);
            }
        }

        return response()->json([
            'success' => true,
            'summary' => [
                'ok' => $okCount,
                'warnings' => $warningCount,
                'errors' => $errorCount,
                'overall' => $errorCount > 0 ? 'error' : ($warningCount > 0 ? 'warning' : 'ok'),
            ],
            'checks' => $checks,
            'fixes' => $fixes,
        ]);
    }

    /**
     * Get suggested fix for an error
     */
    protected function getSuggestedFix(string $key, array $check): array
    {
        $fixes = [
            'evolution_api' => [
                'title' => 'Configure Evolution API',
                'steps' => ['Go to Settings', 'Enter your Evolution API URL (e.g., https://api.evolution.com)', 'Save settings'],
            ],
            'api_key' => [
                'title' => 'Set API Key',
                'steps' => ['Go to Settings', 'Enter your Evolution API Key', 'Save settings'],
            ],
            'instance' => [
                'title' => 'Configure Instance',
                'steps' => ['Go to Settings', 'Enter your WhatsApp instance name', 'Save settings'],
            ],
            'connection' => [
                'title' => 'Connect WhatsApp',
                'steps' => ['Click "Get QR Code" button', 'Scan QR code with your WhatsApp', 'Wait for connection confirmation'],
            ],
            'webhook' => [
                'title' => 'Register Webhook',
                'steps' => ['Go to Evolution API dashboard', 'Set webhook URL to: ' . url('/api/webhook/whatsapp'), 'Enable webhook for messages.upsert event'],
            ],
            'ai_key' => [
                'title' => 'Configure AI Key',
                'steps' => ['Go to Google AI Studio', 'Create API key', 'Add key in Settings → AI Configuration'],
            ],
        ];

        return $fixes[$key] ?? [
            'title' => 'Fix: ' . $check['name'],
            'steps' => [$check['message']],
        ];
    }
}

