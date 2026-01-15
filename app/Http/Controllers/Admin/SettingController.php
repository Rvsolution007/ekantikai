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
            // Lead settings
            'lead_timeout_hours' => $admin->lead_timeout_hours ?? 24,
            // These can remain global or also be moved to admin
            'ai_system_prompt' => Setting::getValue('ai_system_prompt', ''),
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
            'company_name' => $request->input('business_name'),
            'lead_timeout_hours' => (int) $request->input('lead_timeout_hours', 24),
        ]);

        // Other settings can remain global
        $globalSettings = ['ai_system_prompt', 'business_hours'];
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
}
