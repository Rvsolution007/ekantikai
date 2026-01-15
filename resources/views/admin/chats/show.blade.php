@extends('admin.layouts.app')

@section('title', 'Chat with ' . ($user->name ?? $user->number))
@section('page-title', 'Chat')

@section('content')
    <div class="bg-white rounded-xl shadow-sm overflow-hidden h-[calc(100vh-200px)]">
        <div class="flex flex-col h-full">
            <!-- Chat Header -->
            <div class="p-4 border-b flex items-center justify-between bg-gray-50">
                <div class="flex items-center">
                    <a href="{{ route('admin.chats.index') }}" class="mr-4 text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <span class="text-green-600 font-medium">{{ substr($user->name ?? 'U', 0, 1) }}</span>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium text-gray-800">{{ $user->name ?? 'Unknown' }}</p>
                        <p class="text-sm text-gray-500">{{ $user->formatted_number }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="px-2 py-1 text-xs rounded-full
                        {{ $user->bot_enabled ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        Bot: {{ $user->bot_enabled ? 'ON' : 'OFF' }}
                    </span>
                    @if($lead)
                        <a href="{{ route('admin.leads.show', $lead) }}"
                            class="text-sm text-primary-600 hover:text-primary-700">
                            View Lead →
                        </a>
                    @endif
                </div>
            </div>

            <!-- Chat Messages -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-100" id="chat-messages">
                @php $currentDate = null; @endphp
                @foreach($chats as $chat)
                    @if($currentDate !== $chat->created_at->format('Y-m-d'))
                        @php $currentDate = $chat->created_at->format('Y-m-d'); @endphp
                        <div class="flex justify-center">
                            <span class="px-3 py-1 bg-white text-xs text-gray-500 rounded-full shadow-sm">
                                {{ $chat->formatted_date }}
                            </span>
                        </div>
                    @endif

                    <div class="flex {{ $chat->role === 'user' ? 'justify-start' : 'justify-end' }}">
                        <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg shadow-sm
                                {{ $chat->role === 'user' ? 'bg-white text-gray-800' : 'bg-green-500 text-white' }}">
                            @if($chat->isReply() && $chat->quoted_message_text)
                                <div
                                    class="text-xs px-2 py-1 mb-2 rounded border-l-2
                                        {{ $chat->role === 'user' ? 'bg-gray-100 border-gray-400' : 'bg-green-400 border-green-300' }}">
                                    {{ Str::limit($chat->quoted_message_text, 50) }}
                                </div>
                            @endif
                            <p class="text-sm whitespace-pre-wrap">{{ $chat->content }}</p>
                            <p
                                class="text-xs {{ $chat->role === 'user' ? 'text-gray-400' : 'text-green-100' }} text-right mt-1">
                                {{ $chat->formatted_time }}
                                @if(isset($chat->metadata['sent_by_admin']))
                                    <span class="ml-1">👤</span>
                                @endif
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Message Input -->
            <div class="p-4 border-t bg-white">
                <form action="{{ route('admin.chats.send', $user) }}" method="POST" class="flex gap-2">
                    @csrf
                    <input type="text" name="message" placeholder="Type a message..."
                        class="flex-1 px-4 py-3 border rounded-full focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        autocomplete="off">
                    <button type="submit"
                        class="px-6 py-3 bg-green-500 text-white rounded-full hover:bg-green-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Scroll to bottom
            const chatMessages = document.getElementById('chat-messages');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        </script>
    @endpush
@endsection