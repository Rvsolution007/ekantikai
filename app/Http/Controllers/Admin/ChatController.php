<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappUser;
use App\Models\WhatsappChat;
use App\Models\Setting;
use App\Services\WhatsApp\EvolutionApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    /**
     * Display chat list (conversations)
     */
    public function index(Request $request)
    {
        $admin = auth('admin')->user();
        $instance = $admin->whatsapp_instance ?? '';
        $conversations = collect();
        $apiConnected = false;

        // Try to fetch chats from Evolution API
        if (!empty($instance)) {
            try {
                $evolutionApi = new EvolutionApiService($admin);

                // Check connection first
                $connectionState = $evolutionApi->checkConnection($instance);
                $state = $connectionState['instance']['state'] ?? $connectionState['state'] ?? 'unknown';
                $apiConnected = $state === 'open';

                if ($apiConnected) {
                    // Fetch chats from Evolution API
                    $chatsResponse = $this->fetchChatsFromApi($evolutionApi, $instance);
                    $conversations = $chatsResponse;
                }
            } catch (\Exception $e) {
                Log::error('Failed to fetch chats from Evolution API', ['error' => $e->getMessage()]);
            }
        }

        // Also get local database conversations
        $dbConversations = WhatsappUser::withCount('chats')
            ->having('chats_count', '>', 0)
            ->orderBy('last_activity_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($user) {
                $lastChat = $user->chats()->latest()->first();
                return [
                    'id' => $user->id,
                    'remoteJid' => $user->number . '@s.whatsapp.net',
                    'name' => $user->name ?? 'Unknown',
                    'phone' => $user->number,
                    'lastMessage' => $lastChat ? $lastChat->content : '',
                    'lastMessageTime' => $lastChat ? $lastChat->created_at : $user->last_activity_at,
                    'unreadCount' => 0,
                    'profilePicUrl' => null,
                    'source' => 'database',
                ];
            });

        // Merge and deduplicate
        $allConversations = $conversations->merge($dbConversations)
            ->unique('phone')
            ->sortByDesc('lastMessageTime')
            ->values();

        return view('admin.chats.index', [
            'conversations' => $allConversations,
            'apiConnected' => $apiConnected,
            'instance' => $instance,
        ]);
    }

    /**
     * Fetch chats from Evolution API
     */
    protected function fetchChatsFromApi(EvolutionApiService $api, string $instance): \Illuminate\Support\Collection
    {
        try {
            // Evolution API v2 endpoint to fetch chats (POST method)
            $response = $api->makePublicRequest('POST', "/chat/findChats/{$instance}", [
                'where' => new \stdClass(),
            ]);

            return collect($response)->map(function ($chat) {
                // Use remoteJid first (it's the WhatsApp number format)
                $remoteJid = $chat['remoteJid'] ?? $chat['id'] ?? '';
                $phone = preg_replace('/@.*/', '', $remoteJid);

                // Get last message content
                $lastMessage = '';
                if (isset($chat['lastMessage']['message'])) {
                    $msg = $chat['lastMessage']['message'];
                    $lastMessage = $msg['conversation'] ??
                        $msg['extendedTextMessage']['text'] ??
                        $msg['imageMessage']['caption'] ??
                        '[Media]';
                }

                return [
                    'id' => $chat['id'] ?? $remoteJid,
                    'remoteJid' => $remoteJid,
                    'name' => $chat['pushName'] ?? $chat['name'] ?? $phone,
                    'phone' => $phone,
                    'lastMessage' => $lastMessage,
                    'lastMessageTime' => isset($chat['lastMessage']['messageTimestamp'])
                        ? \Carbon\Carbon::createFromTimestamp($chat['lastMessage']['messageTimestamp'])
                        : (isset($chat['updatedAt']) ? \Carbon\Carbon::parse($chat['updatedAt']) : now()),
                    'unreadCount' => $chat['unreadCount'] ?? 0,
                    'profilePicUrl' => $chat['profilePicUrl'] ?? null,
                    'source' => 'api',
                ];
            })->filter(function ($chat) {
                // Filter out broadcasts, status updates and groups
                return !empty($chat['phone']) &&
                    !str_contains($chat['remoteJid'], '@broadcast') &&
                    !str_contains($chat['remoteJid'], '@g.us') &&
                    !str_contains($chat['remoteJid'], 'status@');
            })->take(100); // Limit to 100 chats for performance
        } catch (\Exception $e) {
            Log::error('Failed to fetch chats from API', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    /**
     * Show chat with specific user/number
     */
    public function show(Request $request, $identifier)
    {
        $admin = auth('admin')->user();
        $instance = $admin->whatsapp_instance ?? '';
        $messages = collect();
        $contact = null;

        // Determine if identifier is user ID or phone number
        if (is_numeric($identifier) && WhatsappUser::find($identifier)) {
            $user = WhatsappUser::find($identifier);
            $remoteJid = $user->number . '@s.whatsapp.net';
            $contact = [
                'name' => $user->name ?? 'Unknown',
                'phone' => $user->number,
                'remoteJid' => $remoteJid,
                'bot_enabled' => $user->bot_enabled,
            ];
        } else {
            // It's a phone number or remoteJid
            $remoteJid = str_contains($identifier, '@') ? $identifier : $identifier . '@s.whatsapp.net';
            $phone = preg_replace('/@.*/', '', $remoteJid);
            $user = WhatsappUser::where('number', $phone)->first();
            $contact = [
                'name' => $user->name ?? $phone,
                'phone' => $phone,
                'remoteJid' => $remoteJid,
                'bot_enabled' => $user->bot_enabled ?? true,
            ];
        }

        // Fetch messages from Evolution API
        if (!empty($instance)) {
            try {
                $evolutionApi = new EvolutionApiService($admin);
                $messagesResponse = $evolutionApi->makePublicRequest('POST', "/chat/findMessages/{$instance}", [
                    'where' => [
                        'key' => [
                            'remoteJid' => $remoteJid,
                        ],
                    ],
                    'limit' => 100,
                ]);

                // Handle paginated response structure - messages are in 'messages.records'
                $messagesList = $messagesResponse['messages']['records'] ??
                    $messagesResponse['messages'] ??
                    $messagesResponse;
                if (!is_array($messagesList)) {
                    $messagesList = [];
                }

                $messages = collect($messagesList)->map(function ($msg) use ($instance, $evolutionApi) {
                    $isFromMe = $msg['key']['fromMe'] ?? false;
                    $messageType = $msg['messageType'] ?? 'text';

                    // Extract message content and media info
                    $messageContent = '';
                    $mediaUrl = null;
                    $mediaType = null;
                    $fileName = null;
                    $mimetype = null;

                    if (isset($msg['message'])) {
                        $m = $msg['message'];

                        // Text message
                        if (isset($m['conversation'])) {
                            $messageContent = $m['conversation'];
                        } elseif (isset($m['extendedTextMessage']['text'])) {
                            $messageContent = $m['extendedTextMessage']['text'];
                        }
                        // Image message
                        elseif (isset($m['imageMessage'])) {
                            $mediaType = 'image';
                            $messageContent = $m['imageMessage']['caption'] ?? '';
                            $mediaUrl = $m['imageMessage']['url'] ?? null;
                            $mimetype = $m['imageMessage']['mimetype'] ?? 'image/jpeg';
                        }
                        // Video message
                        elseif (isset($m['videoMessage'])) {
                            $mediaType = 'video';
                            $messageContent = $m['videoMessage']['caption'] ?? '';
                            $mediaUrl = $m['videoMessage']['url'] ?? null;
                            $mimetype = $m['videoMessage']['mimetype'] ?? 'video/mp4';
                        }
                        // Audio message
                        elseif (isset($m['audioMessage'])) {
                            $mediaType = 'audio';
                            $mimetype = $m['audioMessage']['mimetype'] ?? 'audio/ogg';
                        }
                        // Document message
                        elseif (isset($m['documentMessage'])) {
                            $mediaType = 'document';
                            $messageContent = $m['documentMessage']['caption'] ?? '';
                            $fileName = $m['documentMessage']['fileName'] ?? 'Document';
                            $mediaUrl = $m['documentMessage']['url'] ?? null;
                            $mimetype = $m['documentMessage']['mimetype'] ?? 'application/pdf';
                        }
                        // Sticker message
                        elseif (isset($m['stickerMessage'])) {
                            $mediaType = 'sticker';
                            $mediaUrl = $m['stickerMessage']['url'] ?? null;
                        }
                    }

                    return [
                        'id' => $msg['key']['id'] ?? null,
                        'content' => $messageContent,
                        'direction' => $isFromMe ? 'outgoing' : 'incoming',
                        'timestamp' => isset($msg['messageTimestamp'])
                            ? \Carbon\Carbon::createFromTimestamp($msg['messageTimestamp'])
                            : now(),
                        'status' => $msg['MessageUpdate'][0]['status'] ?? $msg['status'] ?? 'sent',
                        'pushName' => $msg['pushName'] ?? null,
                        'messageType' => $messageType,
                        'mediaType' => $mediaType,
                        'mediaUrl' => $mediaUrl,
                        'fileName' => $fileName,
                        'mimetype' => $mimetype,
                    ];
                })->sortBy('timestamp')->values();
            } catch (\Exception $e) {
                Log::error('Failed to fetch messages from API', ['error' => $e->getMessage()]);
            }
        }

        // Get conversations for sidebar
        $conversations = $this->getConversationsForSidebar($instance);

        return view('admin.chats.index', [
            'conversations' => $conversations,
            'activeContact' => $contact,
            'messages' => $messages,
            'instance' => $instance,
            'apiConnected' => true,
        ]);
    }

    /**
     * Get conversations for sidebar
     */
    protected function getConversationsForSidebar(string $instance): \Illuminate\Support\Collection
    {
        $conversations = collect();
        $admin = auth('admin')->user();

        if (!empty($instance)) {
            try {
                $evolutionApi = new EvolutionApiService($admin);
                $conversations = $this->fetchChatsFromApi($evolutionApi, $instance);
            } catch (\Exception $e) {
                // Fallback to database
            }
        }

        if ($conversations->isEmpty()) {
            $conversations = WhatsappUser::withCount('chats')
                ->having('chats_count', '>', 0)
                ->orderBy('last_activity_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function ($user) {
                    $lastChat = $user->chats()->latest()->first();
                    return [
                        'id' => $user->id,
                        'remoteJid' => $user->number . '@s.whatsapp.net',
                        'name' => $user->name ?? 'Unknown',
                        'phone' => $user->number,
                        'lastMessage' => $lastChat ? $lastChat->content : '',
                        'lastMessageTime' => $lastChat ? $lastChat->created_at : $user->last_activity_at,
                        'unreadCount' => 0,
                        'source' => 'database',
                    ];
                });
        }

        return $conversations;
    }

    /**
     * Send manual message
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string|max:4096',
        ]);

        $admin = auth('admin')->user();
        $instance = $admin->whatsapp_instance ?? '';

        if (empty($instance)) {
            return response()->json(['success' => false, 'error' => 'WhatsApp not configured'], 400);
        }

        try {
            $evolutionApi = new EvolutionApiService($admin);
            $phone = preg_replace('/[^0-9]/', '', $request->phone);

            $result = $evolutionApi->sendTextMessage($instance, $phone, $request->message);

            // Save to local database
            $user = WhatsappUser::firstOrCreate(
                ['number' => $phone],
                ['name' => $request->input('name', $phone), 'instance' => $instance]
            );

            WhatsappChat::create([
                'whatsapp_user_id' => $user->id,
                'number' => $phone,
                'role' => 'assistant',
                'content' => $request->message,
                'message_id' => $result['key']['id'] ?? null,
                'metadata' => ['sent_by_admin' => auth('admin')->id()],
            ]);

            $user->touchActivity();

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'messageId' => $result['key']['id'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send message', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Send media message (image, video, document, audio)
     */
    public function sendMedia(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'file' => 'required|file|max:16384', // 16MB max
            'caption' => 'nullable|string|max:1024',
        ]);

        $admin = auth('admin')->user();
        $instance = $admin->whatsapp_instance ?? '';

        if (empty($instance)) {
            return response()->json(['success' => false, 'error' => 'WhatsApp not configured'], 400);
        }

        try {
            $evolutionApi = new EvolutionApiService($admin);
            $phone = preg_replace('/[^0-9]/', '', $request->phone);
            $file = $request->file('file');
            $caption = $request->input('caption', '');

            // Convert file to base64
            $base64 = base64_encode(file_get_contents($file->getRealPath()));
            $mimeType = $file->getMimeType();
            $fileName = $file->getClientOriginalName();
            $base64Data = "data:{$mimeType};base64,{$base64}";

            // Determine media type
            $mediaType = 'document';
            if (str_starts_with($mimeType, 'image/')) {
                $mediaType = 'image';
            } elseif (str_starts_with($mimeType, 'video/')) {
                $mediaType = 'video';
            } elseif (str_starts_with($mimeType, 'audio/')) {
                $mediaType = 'audio';
            }

            // Send via API
            if ($mediaType === 'audio') {
                $result = $evolutionApi->sendAudio($instance, $phone, $base64Data, true);
            } elseif ($mediaType === 'document') {
                $result = $evolutionApi->sendDocument($instance, $phone, $base64Data, $fileName, $caption ?: null);
            } else {
                $result = $evolutionApi->sendMedia($instance, $phone, $base64Data, $mediaType, $caption ?: null, $fileName);
            }

            // Save to local database
            $user = WhatsappUser::firstOrCreate(
                ['number' => $phone],
                ['name' => $phone, 'instance' => $instance]
            );

            WhatsappChat::create([
                'whatsapp_user_id' => $user->id,
                'number' => $phone,
                'role' => 'assistant',
                'content' => $caption ?: "[{$mediaType}]",
                'message_id' => $result['key']['id'] ?? null,
                'metadata' => [
                    'sent_by_admin' => auth('admin')->id(),
                    'media_type' => $mediaType,
                    'file_name' => $fileName,
                ],
            ]);

            $user->touchActivity();

            return response()->json([
                'success' => true,
                'message' => ucfirst($mediaType) . ' sent successfully',
                'messageId' => $result['key']['id'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send media', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
