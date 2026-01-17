<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Catalogue;
use App\Models\Customer;
use App\Models\Admin;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\WhatsappChat;
use App\Services\AIService;
use App\Services\BotControlService;
use App\Services\LanguageDetectionService;
use App\Services\MessageProcessorService;
use App\Services\QuestionnaireService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WebhookController extends Controller
{
    protected BotControlService $botControlService;
    protected LanguageDetectionService $languageService;

    public function __construct()
    {
        $this->botControlService = new BotControlService();
        $this->languageService = new LanguageDetectionService();
    }

    /**
     * Handle incoming WhatsApp webhook from Evolution API
     */
    public function handle(Request $request, string $instanceName = 'default')
    {
        try {
            $data = $request->all();

            // DEBUG: Log ALL incoming webhook data for troubleshooting
            Log::channel('daily')->info('=== WEBHOOK CALL RECEIVED ===', [
                'instance' => $instanceName,
                'ip' => $request->ip(),
                'event' => $data['event'] ?? 'unknown',
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'raw_data' => json_encode($data),
            ]);

            // Also write to a dedicated webhook log file
            file_put_contents(
                storage_path('logs/webhook_debug.log'),
                date('Y-m-d H:i:s') . " | Instance: {$instanceName} | Event: " . ($data['event'] ?? 'unknown') . " | IP: " . $request->ip() . "\n",
                FILE_APPEND
            );

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

            // DEDUPLICATION: Skip if message already processed
            $cacheKey = 'processed_msg_' . ($messageData['messageId'] ?? md5($messageData['phone'] . $messageData['content'] . $messageData['timestamp']));
            if (Cache::has($cacheKey)) {
                return response()->json(['status' => 'ignored', 'reason' => 'duplicate message']);
            }
            Cache::put($cacheKey, true, 60);

            // Skip if message matches bot's greeting patterns (self-reply prevention)
            if ($this->isBotMessage($messageData['content'])) {
                return response()->json(['status' => 'ignored', 'reason' => 'bot message pattern detected']);
            }

            // CHECK BOT CONTROL COMMANDS (Point 2)
            $controlResult = $this->botControlService->handleControlMessage(
                $tenant,
                $messageData['phone'],
                $messageData['content']
            );
            if ($controlResult) {
                // This is a control message, send confirmation
                if ($controlResult['success']) {
                    $this->sendResponse($tenant, $messageData['phone'], $controlResult['message']);
                }
                return response()->json([
                    'status' => 'control_command',
                    'result' => $controlResult
                ]);
            }

            // Get or create customer
            $customer = $this->getOrCreateCustomer($tenant->id, $messageData['phone'], $messageData['name']);

            // CHECK IF BOT IS STOPPED (Point 2)
            if ($customer->isBotStopped()) {
                // Still save message to database but don't respond
                $this->saveMessage($tenant->id, $customer->id, 'user', $messageData['content'], $messageData);
                return response()->json(['status' => 'ignored', 'reason' => 'bot stopped for this user']);
            }

            // Get or create lead based on timeout setting
            $lead = $customer->getOrCreateLead();

            // CHECK IF BOT IS ACTIVE FOR THIS LEAD (Point 7)
            if (!$lead->bot_active) {
                $this->saveMessage($tenant->id, $customer->id, 'user', $messageData['content'], $messageData);
                return response()->json(['status' => 'ignored', 'reason' => 'bot inactive for this lead']);
            }

            // Detect language (Point 8.4)
            $detectedLanguage = $this->languageService->detect($messageData['content']);
            $customer->setLanguage($detectedLanguage);
            $lead->update(['detected_language' => $detectedLanguage]);

            // Save incoming message with reply info (Point 4 & 5)
            $this->saveMessage($tenant->id, $customer->id, 'user', $messageData['content'], $messageData);

            // PROCESS WITH AI (Point 8)
            $aiService = new AIService();
            $aiResponse = $aiService->processMessageEnhanced(
                $tenant,
                $customer,
                $lead,
                $messageData['content'],
                [
                    'reply_to_content' => $messageData['replyToContent'] ?? null,
                ]
            );

            // Update lead from AI response (Point 8.5, 8.8)
            if ($aiResponse['success']) {
                $this->processAIResponse($tenant, $customer, $lead, $aiResponse);
            }

            // Update customer's last activity
            $customer->updateLastActivity();

            // Get response message
            $responseMessage = $aiResponse['response_message'] ?? '';

            // CHECK FOR CATALOGUE IMAGE (Point 12)
            $catalogueMedia = null;
            if (!empty($aiResponse['unique_field_mentioned'])) {
                $catalogueMedia = $aiService->checkCatalogueForUniqueField(
                    $tenant->id,
                    $aiResponse['unique_field_mentioned']
                );
            }

            // Send response if any
            if (!empty($responseMessage)) {
                // Send catalogue image first if available
                if ($catalogueMedia && !empty($catalogueMedia['image_url'])) {
                    $this->sendImageResponse($tenant, $messageData['phone'], $catalogueMedia['image_url']);
                }

                $this->sendResponse($tenant, $messageData['phone'], $responseMessage);

                // Save bot response (Point 5)
                $this->saveMessage($tenant->id, $customer->id, 'assistant', $responseMessage, [
                    'ai_response' => true,
                    'detected_language' => $detectedLanguage,
                ]);
            }

            // CHECK IF ALL REQUIRED QUESTIONS COMPLETE (Point 7)
            if ($aiResponse['all_required_complete'] ?? false) {
                $lead->markBotComplete();
            }

            // Update lead score after processing
            $lead->calculateScore();

            return response()->json([
                'status' => 'success',
                'lead_id' => $lead->id,
                'detected_language' => $detectedLanguage,
                'bot_active' => $lead->bot_active,
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
     * Process AI response and update lead/customer
     */
    protected function processAIResponse(Admin $tenant, Customer $customer, Lead $lead, array $aiResponse): void
    {
        // Update extracted data (Point 8.8)
        if (!empty($aiResponse['extracted_data'])) {
            foreach ($aiResponse['extracted_data'] as $key => $value) {
                if ($value !== null) {
                    $lead->addCollectedData($key, $value);
                }
            }
        }

        // Handle product confirmations (Point 8.3)
        if (!empty($aiResponse['product_confirmations'])) {
            Log::debug('Saving product confirmations', [
                'lead_id' => $lead->id,
                'count' => count($aiResponse['product_confirmations']),
                'data' => $aiResponse['product_confirmations'],
            ]);
            foreach ($aiResponse['product_confirmations'] as $product) {
                $lead->addProductConfirmation($product);
            }
        }

        // Handle product actions (remove/update)
        if (!empty($aiResponse['product_actions'])) {
            $action = $aiResponse['product_actions'];
            if ($action['action'] === 'remove' && isset($action['index'])) {
                $lead->updateProductConfirmation($action['index'], null);
            }
        }

        // Update lead status (Point 8.5)
        if (!empty($aiResponse['lead_status_suggestion'])) {
            $status = LeadStatus::where('admin_id', $tenant->id)
                ->where('name', $aiResponse['lead_status_suggestion'])
                ->first();
            if ($status) {
                $lead->updateLeadStatus($status->id);
            }
        }
    }

    /**
     * Extract message data from webhook payload with reply detection (Point 4)
     */
    protected function extractMessageData(array $data): ?array
    {
        $message = $data['data'] ?? $data;

        if (isset($message['key'])) {
            $remoteJid = $message['key']['remoteJid'] ?? '';
            $phone = $this->cleanPhone($remoteJid);

            $msgContent = $message['message'] ?? [];

            // REPLY DETECTION (Point 4)
            $replyToContent = null;
            $replyToMessageId = null;

            // Check for contextInfo in extendedTextMessage
            if (isset($msgContent['extendedTextMessage']['contextInfo'])) {
                $contextInfo = $msgContent['extendedTextMessage']['contextInfo'];
                $replyToMessageId = $contextInfo['stanzaId'] ?? null;
                $replyToContent = $contextInfo['quotedMessage']['conversation']
                    ?? $contextInfo['quotedMessage']['extendedTextMessage']['text']
                    ?? null;
            }

            return [
                'phone' => $phone,
                'name' => $message['pushName'] ?? $phone,
                'content' => $this->extractContent($msgContent),
                'fromMe' => $message['key']['fromMe'] ?? false,
                'messageId' => $message['key']['id'] ?? null,
                'whatsappMessageId' => $message['key']['id'] ?? null,
                'timestamp' => $message['messageTimestamp'] ?? time(),
                'isReply' => !empty($replyToMessageId),
                'replyToMessageId' => $replyToMessageId,
                'replyToContent' => $replyToContent,
            ];
        }

        // Legacy format
        if (isset($message['messages']) && is_array($message['messages'])) {
            $msg = $message['messages'][0] ?? null;
            if ($msg) {
                $msgContent = $msg['message'] ?? [];

                // Reply detection for legacy
                $replyToContent = null;
                $replyToMessageId = null;
                if (isset($msgContent['extendedTextMessage']['contextInfo'])) {
                    $contextInfo = $msgContent['extendedTextMessage']['contextInfo'];
                    $replyToMessageId = $contextInfo['stanzaId'] ?? null;
                    $replyToContent = $contextInfo['quotedMessage']['conversation'] ?? null;
                }

                return [
                    'phone' => $this->cleanPhone($msg['key']['remoteJid'] ?? ''),
                    'name' => $msg['pushName'] ?? '',
                    'content' => $this->extractContent($msgContent),
                    'fromMe' => $msg['key']['fromMe'] ?? false,
                    'messageId' => $msg['key']['id'] ?? null,
                    'whatsappMessageId' => $msg['key']['id'] ?? null,
                    'timestamp' => $msg['messageTimestamp'] ?? time(),
                    'isReply' => !empty($replyToMessageId),
                    'replyToMessageId' => $replyToMessageId,
                    'replyToContent' => $replyToContent,
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
        if (isset($message['conversation'])) {
            return $message['conversation'];
        }

        if (isset($message['extendedTextMessage']['text'])) {
            return $message['extendedTextMessage']['text'];
        }

        if (isset($message['buttonsResponseMessage']['selectedButtonId'])) {
            return $message['buttonsResponseMessage']['selectedButtonId'];
        }

        if (isset($message['listResponseMessage']['singleSelectReply']['selectedRowId'])) {
            return $message['listResponseMessage']['singleSelectReply']['selectedRowId'];
        }

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
        $admin = Admin::where('whatsapp_instance', $instanceName)
            ->where('is_active', true)
            ->first();

        if ($admin) {
            Log::debug('Found admin by whatsapp_instance', [
                'instance' => $instanceName,
                'admin_id' => $admin->id,
                'admin_name' => $admin->name,
                'catalogue_count' => $admin->catalogues()->where('is_active', true)->count(),
            ]);
            return $admin;
        }

        if (\Schema::hasTable('whatsapp_instances')) {
            $instance = DB::table('whatsapp_instances')
                ->where('instance_name', $instanceName)
                ->first();

            if ($instance) {
                $admin = Admin::find($instance->admin_id);
                if ($admin) {
                    Log::debug('Found admin via whatsapp_instances table', [
                        'instance' => $instanceName,
                        'admin_id' => $admin->id,
                    ]);
                }
                return $admin;
            }
        }

        Log::warning('Could not find tenant by instance name, using first active', [
            'instance' => $instanceName
        ]);
        $fallbackAdmin = Admin::where('is_active', true)->first();
        if ($fallbackAdmin) {
            Log::debug('Using fallback admin', [
                'admin_id' => $fallbackAdmin->id,
                'admin_name' => $fallbackAdmin->name,
                'catalogue_count' => $fallbackAdmin->catalogues()->where('is_active', true)->count(),
            ]);
        }
        return $fallbackAdmin;
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
                'bot_stopped_by_user' => false,
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
     * Save chat message with reply info (Point 4 & 5)
     */
    protected function saveMessage(int $adminId, int $customerId, string $role, string $content, array $metadata = []): void
    {
        WhatsappChat::create([
            'admin_id' => $adminId,
            'customer_id' => $customerId,
            'whatsapp_user_id' => $customerId, // Use customer ID as whatsapp_user_id
            'number' => $metadata['phone'] ?? null,
            'role' => $role,
            'content' => $content,
            'whatsapp_message_id' => $metadata['whatsappMessageId'] ?? $metadata['messageId'] ?? null,
            'message_id' => $metadata['messageId'] ?? null,
            'is_reply' => $metadata['isReply'] ?? false,
            'reply_to_message_id' => $metadata['replyToMessageId'] ?? null,
            'reply_to_content' => $metadata['replyToContent'] ?? null,
            'metadata' => $metadata,
        ]);
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
     * Send image response via WhatsApp (Point 12)
     */
    protected function sendImageResponse(Admin $tenant, string $phone, string $imageUrl): void
    {
        try {
            $instance = $tenant->whatsapp_instance;
            if (empty($instance)) {
                return;
            }

            $evolutionService = new \App\Services\WhatsApp\EvolutionApiService($tenant);
            $evolutionService->sendMediaMessage($instance, $phone, $imageUrl, 'image');

            Log::info('Bot image sent', [
                'admin_id' => $tenant->id,
                'phone' => $phone,
                'image_url' => $imageUrl
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp image', [
                'error' => $e->getMessage(),
                'phone' => $phone,
            ]);
        }
    }

    /**
     * Clean phone number
     */
    protected function cleanPhone(string $jid): string
    {
        $phone = preg_replace('/@.*$/', '', $jid);
        return preg_replace('/[^0-9]/', '', $phone);
    }

    /**
     * Check if message is from bot (to prevent self-reply)
     */
    protected function isBotMessage(string $message): bool
    {
        $botPatterns = [
            'Hello! 🙏 How can I help you?',
            'Namaste! 🙏',
            'How can I help you?',
            'What product are you looking for?',
            'Main aapki kya madad kar sakta hoon?',
            'Aapko kaunsa product chahiye?',
            'Thank you! 🙏 Let me know if you need anything else.',
            'Dhanyavaad! 🙏',
            'Our team will contact you soon.',
            'I have collected all the information.',
        ];

        foreach ($botPatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
