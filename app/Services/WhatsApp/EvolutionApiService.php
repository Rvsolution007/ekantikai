<?php

namespace App\Services\WhatsApp;

use App\Models\Admin;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionApiService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected string $defaultInstance;

    public function __construct(?Admin $admin = null)
    {
        if ($admin) {
            // Use per-admin settings
            $this->apiUrl = rtrim($admin->whatsapp_api_url ?? '', '/');
            $this->apiKey = $admin->whatsapp_api_key ?? '';
            $this->defaultInstance = $admin->whatsapp_instance ?? '';
        } else {
            // Fallback to global settings
            $this->apiUrl = rtrim(Setting::getValue('whatsapp_api_url', config('services.whatsapp.api_url', '')), '/');
            $this->apiKey = Setting::getValue('whatsapp_api_key', config('services.whatsapp.api_key', ''));
            $this->defaultInstance = Setting::getValue('whatsapp_instance', config('services.whatsapp.instance', ''));
        }
    }

    /**
     * Send a text message
     */
    public function sendTextMessage(string $instance, string $number, string $message): array
    {
        $instance = $instance ?: $this->defaultInstance;

        // Evolution API v2 uses 'text' property directly, not nested in 'textMessage'
        $response = $this->makeRequest('POST', "/message/sendText/{$instance}", [
            'number' => $this->formatNumber($number),
            'text' => $message,
        ]);

        return $response;
    }

    /**
     * Send media (image, video, audio) via URL or base64
     */
    public function sendMedia(string $instance, string $number, string $media, string $mediaType = 'image', ?string $caption = null, ?string $fileName = null): array
    {
        $instance = $instance ?: $this->defaultInstance;

        // Evolution API v2 uses 'media' directly
        $payload = [
            'number' => $this->formatNumber($number),
            'mediatype' => $mediaType,
            'media' => $media, // Can be URL or base64
        ];

        if ($caption) {
            $payload['caption'] = $caption;
        }

        if ($fileName) {
            $payload['fileName'] = $fileName;
        }

        return $this->makeRequest('POST', "/message/sendMedia/{$instance}", $payload);
    }

    /**
     * Send a document (PDF, etc.)
     */
    public function sendDocument(string $instance, string $number, string $documentData, string $fileName, ?string $caption = null): array
    {
        $instance = $instance ?: $this->defaultInstance;

        $payload = [
            'number' => $this->formatNumber($number),
            'mediatype' => 'document',
            'media' => $documentData, // URL or base64
            'fileName' => $fileName,
        ];

        if ($caption) {
            $payload['caption'] = $caption;
        }

        return $this->makeRequest('POST', "/message/sendMedia/{$instance}", $payload);
    }

    /**
     * Send audio message (voice note)
     */
    public function sendAudio(string $instance, string $number, string $audioData, bool $ptt = true): array
    {
        $instance = $instance ?: $this->defaultInstance;

        $payload = [
            'number' => $this->formatNumber($number),
            'mediatype' => 'audio',
            'media' => $audioData, // URL or base64
            'ptt' => $ptt, // Push to talk (voice note style)
        ];

        return $this->makeRequest('POST', "/message/sendMedia/{$instance}", $payload);
    }

    /**
     * Check if instance is connected
     */
    public function checkConnection(string $instance): array
    {
        $instance = $instance ?: $this->defaultInstance;

        // Don't URL encode - Evolution API expects exact instance name
        return $this->makeRequest('GET', "/instance/connectionState/{$instance}");
    }

    /**
     * Get QR code for connecting
     */
    public function getQrCode(string $instance): array
    {
        $instance = $instance ?: $this->defaultInstance;

        // Try the connect endpoint first (Evolution API v2)
        try {
            $result = $this->makeRequest('GET', "/instance/connect/{$instance}");
            if (!empty($result)) {
                return $result;
            }
        } catch (\Exception $e) {
            // Try alternative endpoint
        }

        // Try fetch instance to get QR (alternative method)
        return $this->makeRequest('GET', "/instance/fetchInstances", ['instanceName' => $instance]);
    }

    /**
     * Logout/Disconnect WhatsApp instance
     */
    public function logout(string $instance): array
    {
        $instance = $instance ?: $this->defaultInstance;

        return $this->makeRequest('DELETE', "/instance/logout/{$instance}");
    }

    /**
     * Restart instance (disconnect and get new QR)
     */
    public function restartInstance(string $instance): array
    {
        $instance = $instance ?: $this->defaultInstance;

        return $this->makeRequest('PUT', "/instance/restart/{$instance}");
    }

    /**
     * Get webhook configuration for an instance
     */
    public function getWebhook(string $instance): array
    {
        $instance = $instance ?: $this->defaultInstance;

        try {
            return $this->makeRequest('GET', "/webhook/find/{$instance}");
        } catch (\Exception $e) {
            // Try alternative endpoint
            try {
                return $this->makeRequest('GET', "/instance/fetchInstances", ['instanceName' => $instance]);
            } catch (\Exception $e2) {
                return ['error' => $e->getMessage()];
            }
        }
    }

    /**
     * Format phone number for WhatsApp
     */
    protected function formatNumber(string $number): string
    {
        // Remove all non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $number);

        // Remove leading zeros
        $number = ltrim($number, '0');

        // Add country code if not present (assuming India)
        if (strlen($number) === 10) {
            $number = '91' . $number;
        }

        // Add @s.whatsapp.net suffix
        return $number . '@s.whatsapp.net';
    }

    /**
     * Public wrapper for making API requests (for use outside this class)
     */
    public function makePublicRequest(string $method, string $endpoint, array $data = []): array
    {
        return $this->makeRequest($method, $endpoint, $data);
    }

    /**
     * Make HTTP request to Evolution API
     */
    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        if (empty($this->apiUrl)) {
            throw new \Exception('WhatsApp API URL is not configured');
        }

        $url = $this->apiUrl . $endpoint;

        try {
            $request = Http::timeout(30)->withHeaders([
                'apikey' => $this->apiKey,
                'Content-Type' => 'application/json',
            ]);

            Log::info('Evolution API Request', ['url' => $url, 'method' => $method]);

            if ($method === 'GET') {
                $response = $request->get($url, $data);
            } elseif ($method === 'DELETE') {
                $response = $request->delete($url, $data);
            } elseif ($method === 'PUT') {
                $response = $request->put($url, $data);
            } else {
                $response = $request->post($url, $data);
            }

            Log::info('Evolution API Response', [
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 500)
            ]);

            if ($response->failed()) {
                Log::error('Evolution API Error', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new \Exception('Evolution API request failed: ' . $response->body());
            }

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Evolution API Exception', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}

