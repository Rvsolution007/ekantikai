<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Admin;
use App\Models\WhatsappChat;
use App\Services\ActionProcessorService;
use App\Services\AIService;
use App\Services\MessageProcessorService;
use App\Services\QuestionnaireService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle incoming WhatsApp webhook from Evolution API
     */
    public function handle(Request $request, string $instanceName = 'default')
    {
        try {
            $data = $request->all();

            Log::info('Webhook received', [
                'instance' => $instanceName,
                'event' => $data['event'] ?? 'unknown'
            ]);

            // Get event type
            $event = $data['event'] ?? null;

            // Only process messages
            if (!in_array($event, ['messages.upsert', 'message', 'MESSAGES_UPSERT'])) {
                return response()->json(['status' => 'ignored', 'reason' => 'not a message event']);
            }

            // Extract message data
            $messageData = $this->extractMessageData($data);
            if (!$messageData || empty($messageData['content'])) {
                return response()->json(['status' => 'ignored', 'reason' => 'no message data']);
            }

            // Find tenant by instance name
            $tenant = $this->findTenantByInstance($instanceName);
            if (!$tenant) {
                Log::warning('Tenant not found for instance', ['instance' => $instanceName]);
                return response()->json(['status' => 'error', 'reason' => 'tenant not found']);
            }

            // Skip if from self (sent messages)
            if ($messageData['fromMe']) {
                return response()->json(['status' => 'ignored', 'reason' => 'self message']);
            }

            // Get or create customer
            $customer = $this->getOrCreateCustomer($tenant->id, $messageData['phone'], $messageData['name']);

            // Get or create lead based on timeout setting
            $lead = $customer->getOrCreateLead();

            // Save incoming message
            $this->saveMessage($tenant->id, $customer->id, 'user', $messageData['content'], $messageData);

            // Check if bot is enabled
            if (!$customer->bot_enabled) {
                return response()->json(['status' => 'ignored', 'reason' => 'bot disabled']);
            }

            // Process message with MessageProcessor (pass lead for data collection)
            $processor = new MessageProcessorService($tenant, $customer, $lead);
            $response = $processor->process($messageData['content']);

            // Update customer's last activity (for lead timeout calculation)
            $customer->updateLastActivity();

            // Send response if any
            if (!empty($response['message'])) {
                $this->sendResponse($tenant, $messageData['phone'], $response['message']);

                // Save bot response
                $this->saveMessage($tenant->id, $customer->id, 'assistant', $response['message']);
            }

            // Update lead score after processing
            $lead->calculateScore();

            return response()->json([
                'status' => 'success',
                'lead_id' => $lead->id,
                'response' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('Webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract message data from webhook payload
     */
    protected function extractMessageData(array $data): ?array
    {
        // Evolution API v2 format
        $message = $data['data'] ?? $data;

        if (isset($message['key'])) {
            // New message format
            $remoteJid = $message['key']['remoteJid'] ?? '';
            $phone = $this->cleanPhone($remoteJid);

            return [
                'phone' => $phone,
                'name' => $message['pushName'] ?? $phone,
                'content' => $this->extractContent($message['message'] ?? []),
                'fromMe' => $message['key']['fromMe'] ?? false,
                'messageId' => $message['key']['id'] ?? null,
                'timestamp' => $message['messageTimestamp'] ?? time(),
            ];
        }

        // Legacy format
        if (isset($message['messages']) && is_array($message['messages'])) {
            $msg = $message['messages'][0] ?? null;
            if ($msg) {
                return [
                    'phone' => $this->cleanPhone($msg['key']['remoteJid'] ?? ''),
                    'name' => $msg['pushName'] ?? '',
                    'content' => $this->extractContent($msg['message'] ?? []),
                    'fromMe' => $msg['key']['fromMe'] ?? false,
                    'messageId' => $msg['key']['id'] ?? null,
                    'timestamp' => $msg['messageTimestamp'] ?? time(),
                ];
            }
        }

        return null;
    }

    /**
     * Extract text content from message
     */
    protected function extractContent(array $message): string
    {
        // Text message
        if (isset($message['conversation'])) {
            return $message['conversation'];
        }

        if (isset($message['extendedTextMessage']['text'])) {
            return $message['extendedTextMessage']['text'];
        }

        // Button response
        if (isset($message['buttonsResponseMessage']['selectedButtonId'])) {
            return $message['buttonsResponseMessage']['selectedButtonId'];
        }

        // List response
        if (isset($message['listResponseMessage']['singleSelectReply']['selectedRowId'])) {
            return $message['listResponseMessage']['singleSelectReply']['selectedRowId'];
        }

        // Image/Video/Document with caption
        foreach (['imageMessage', 'videoMessage', 'documentMessage'] as $type) {
            if (isset($message[$type]['caption'])) {
                return $message[$type]['caption'];
            }
        }

        return '';
    }

    /**
     * Find tenant by WhatsApp instance name
     */
    protected function findTenantByInstance(string $instanceName): ?Admin
    {
        // PRIMARY: Check admins table where instance is stored directly
        $admin = Admin::where('whatsapp_instance', $instanceName)
            ->where('is_active', true)
            ->first();

        if ($admin) {
            return $admin;
        }

        // SECONDARY: Check in whatsapp_instances table if exists
        if (\Schema::hasTable('whatsapp_instances')) {
            $instance = \DB::table('whatsapp_instances')
                ->where('instance_name', $instanceName)
                ->first();

            if ($instance) {
                return Admin::find($instance->admin_id);
            }
        }

        // FALLBACK: Default to first active tenant
        Log::warning('Could not find tenant by instance name, using first active', [
            'instance' => $instanceName
        ]);
        return Admin::where('is_active', true)->first();
    }

    /**
     * Get or create customer
     */
    protected function getOrCreateCustomer(int $adminId, string $phone, ?string $name): Customer
    {
        $customer = Customer::where('admin_id', $adminId)
            ->where('phone', $phone)
            ->first();

        if (!$customer) {
            $customer = Customer::create([
                'admin_id' => $adminId,
                'phone' => $phone,
                'name' => $name,
                'bot_enabled' => true,
                'detected_language' => 'hi',
                'last_activity_at' => now(),
            ]);
        } else {
            $customer->update([
                'name' => $name ?: $customer->name,
                'last_activity_at' => now(),
            ]);
        }

        return $customer;
    }

    /**
     * Save chat message
     */
    protected function saveMessage(int $adminId, int $customerId, string $role, string $content, array $metadata = []): void
    {
        \DB::table('chat_messages')->insert([
            'admin_id' => $adminId,
            'customer_id' => $customerId,
            'role' => $role,
            'content' => $content,
            'message_id' => $metadata['messageId'] ?? null,
            'metadata' => json_encode($metadata),
            'created_at' => now(),
        ]);
    }

    /**
     * Process message and get response
     */
    protected function processMessage(Admin $tenant, Customer $customer, string $message): array
    {
        // Initialize questionnaire service
        $questionnaireService = new QuestionnaireService($tenant->id, $customer);

        // Check for casual message (hi, hello, thanks)
        if ($this->isCasualMessage($message)) {
            return [
                'type' => 'casual',
                'message' => $this->getCasualResponse($message, $customer->detected_language),
                'action' => null
            ];
        }

        // Get current state
        $state = $customer->getOrCreateState();
        $currentField = $state->current_field;

        // If we're waiting for a field response
        if ($currentField) {
            // Process the response
            $result = $questionnaireService->processResponse($currentField, $message);

            // Get next question
            $next = $questionnaireService->getNextQuestionSmart();

            return [
                'type' => $next['type'],
                'message' => $next['question'],
                'action' => $result,
                'field' => $next['field']
            ];
        }

        // Try to extract product/model from message (AI would do this)
        $extracted = $this->simpleExtract($tenant->id, $message);

        if ($extracted) {
            // Save extracted values to state
            foreach ($extracted as $field => $value) {
                $questionnaireService->processResponse($field, $value);
            }
        }

        // Get next question
        $next = $questionnaireService->getNextQuestionSmart();

        // Update current field in state
        if ($next['field']) {
            $state->current_field = $next['field'];
            $state->save();
        }

        return [
            'type' => $next['type'],
            'message' => $next['question'],
            'action' => null,
            'field' => $next['field']
        ];
    }

    /**
     * Check if message is casual (greeting, thanks, etc.)
     */
    protected function isCasualMessage(string $message): bool
    {
        $message = strtolower(trim($message));
        $casualPatterns = [
            'hi',
            'hello',
            'hey',
            'hii',
            'hiii',
            'namaste',
            'namaskar',
            'jai shree krishna',
            'thanks',
            'thank you',
            'dhanyavaad',
            'shukriya',
            'ok',
            'okay',
            'thik hai',
            'theek hai',
            'good morning',
            'good evening',
            'good night',
            '👋',
            '🙏',
            '😊'
        ];

        foreach ($casualPatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get casual response
     */
    protected function getCasualResponse(string $message, string $language = 'hi'): string
    {
        $responses = [
            'hi' => [
                'hi' => 'Namaste! 🙏 Main Datsun Hardware ka AI assistant hoon. Aapko kya chahiye?',
                'en' => 'Hello! 🙏 I am Datsun Hardware AI assistant. How can I help you?'
            ],
            'thanks' => [
                'hi' => 'Dhanyavaad! 🙏 Kuch aur chahiye toh batayein.',
                'en' => 'Thank you! 🙏 Let me know if you need anything else.'
            ]
        ];

        $type = str_contains(strtolower($message), 'thank') ? 'thanks' : 'hi';
        return $responses[$type][$language] ?? $responses[$type]['hi'];
    }

    /**
     * Simple extraction without AI (fallback)
     */
    protected function simpleExtract(int $adminId, string $message): array
    {
        $extracted = [];
        $message = strtolower($message);

        // Get catalogue items for matching
        $catalogues = \App\Models\Catalogue::where('admin_id', $adminId)->get();

        foreach ($catalogues as $item) {
            $category = strtolower($item->data['category'] ?? $item->category ?? '');
            $model = strtolower($item->data['model_code'] ?? $item->model ?? '');

            if ($category && str_contains($message, $category)) {
                $extracted['category'] = $item->data['category'] ?? $item->category;
            }

            if ($model && str_contains($message, $model)) {
                $extracted['model'] = $item->data['model_code'] ?? $item->model;
            }
        }

        // Extract quantity
        if (preg_match('/(\d+)\s*(pcs|pieces|piece|pc)/i', $message, $matches)) {
            $extracted['qty'] = $matches[1];
        }

        return $extracted;
    }

    /**
     * Send response via WhatsApp
     */
    protected function sendResponse(Admin $tenant, string $phone, string $message): void
    {
        try {
            $instance = $tenant->whatsapp_instance;
            if (empty($instance)) {
                Log::warning('No WhatsApp instance configured for tenant', ['admin_id' => $tenant->id]);
                return;
            }

            $evolutionService = new \App\Services\WhatsApp\EvolutionApiService($tenant);
            $evolutionService->sendTextMessage($instance, $phone, $message);

            Log::info('Bot response sent', [
                'admin_id' => $tenant->id,
                'phone' => $phone,
                'message_length' => strlen($message)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp message', [
                'error' => $e->getMessage(),
                'phone' => $phone,
                'admin_id' => $tenant->id
            ]);
        }
    }

    /**
     * Clean phone number
     */
    protected function cleanPhone(string $jid): string
    {
        // Remove @s.whatsapp.net or @c.us
        $phone = preg_replace('/@.*$/', '', $jid);
        // Remove non-digits
        return preg_replace('/[^0-9]/', '', $phone);
    }
}
