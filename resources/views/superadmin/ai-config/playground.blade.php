@extends('superadmin.layouts.app')

@section('title', 'AI Playground')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-white">AI Playground</h1>
                <p class="text-gray-400">Test the AI model with custom prompts</p>
            </div>
            <a href="{{ route('superadmin.ai-config.index') }}"
                class="px-4 py-2 bg-gray-500/20 text-gray-400 rounded-xl hover:bg-gray-500/30 transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Config
            </a>
        </div>

        <!-- Model Info Card -->
        <div class="glass rounded-2xl p-6">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-white">Current Model</h3>
                    <p class="text-gray-400">
                        <span class="text-blue-400 font-medium">{{ $provider ?? 'vertex' }}</span> /
                        <span class="text-purple-400 font-medium">{{ $model ?? 'gemini-2.0-flash' }}</span>
                    </p>
                </div>
                <div class="ml-auto flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full {{ $aiWorking ? 'bg-green-400 animate-pulse' : 'bg-red-400' }}"></div>
                    <span class="text-sm {{ $aiWorking ? 'text-green-400' : 'text-red-400' }}">
                        {{ $aiWorking ? 'Connected' : 'Disconnected' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Chat Container -->
        <div class="glass rounded-2xl overflow-hidden">
            <!-- Chat Messages -->
            <div id="chatMessages" class="h-96 overflow-y-auto p-6 space-y-4">
                <div class="text-center text-gray-500 py-8">
                    <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <p>Start a conversation with the AI model</p>
                    <p class="text-sm mt-2">Type your message below and press Enter or click Send</p>
                </div>
            </div>

            <!-- Input Area -->
            <div class="border-t border-white/10 p-4">
                <form id="chatForm" class="flex gap-4">
                    <input type="text" id="userInput"
                        class="flex-1 input-dark px-4 py-3 rounded-xl text-white placeholder-gray-500"
                        placeholder="Type your message..." autocomplete="off">
                    <button type="submit" id="sendBtn"
                        class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-xl hover:from-blue-600 hover:to-purple-600 transition-all flex items-center gap-2 font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        Send
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick Prompts -->
        <div class="glass rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Quick Prompts</h3>
            <div class="flex flex-wrap gap-2">
                <button onclick="setPrompt('Hello! How are you?')"
                    class="px-4 py-2 bg-gray-500/20 text-gray-300 rounded-lg hover:bg-gray-500/30 transition-colors text-sm">
                    Hello! How are you?
                </button>
                <button onclick="setPrompt('What AI model are you?')"
                    class="px-4 py-2 bg-gray-500/20 text-gray-300 rounded-lg hover:bg-gray-500/30 transition-colors text-sm">
                    What AI model are you?
                </button>
                <button onclick="setPrompt('Explain quantum computing in simple terms')"
                    class="px-4 py-2 bg-gray-500/20 text-gray-300 rounded-lg hover:bg-gray-500/30 transition-colors text-sm">
                    Explain quantum computing
                </button>
                <button onclick="setPrompt('Write a short poem about technology')"
                    class="px-4 py-2 bg-gray-500/20 text-gray-300 rounded-lg hover:bg-gray-500/30 transition-colors text-sm">
                    Write a poem
                </button>
            </div>
        </div>
    </div>

    <script>
        const chatMessages = document.getElementById('chatMessages');
        const chatForm = document.getElementById('chatForm');
        const userInput = document.getElementById('userInput');
        const sendBtn = document.getElementById('sendBtn');
        let isFirstMessage = true;

        function setPrompt(text) {
            userInput.value = text;
            userInput.focus();
        }

        function addMessage(role, content) {
            if (isFirstMessage) {
                chatMessages.innerHTML = '';
                isFirstMessage = false;
            }

            const messageDiv = document.createElement('div');
            messageDiv.className = role === 'user'
                ? 'flex justify-end'
                : 'flex justify-start';

            const bubble = document.createElement('div');
            bubble.className = role === 'user'
                ? 'max-w-2xl px-4 py-3 rounded-2xl bg-gradient-to-r from-blue-500 to-purple-500 text-white'
                : 'max-w-2xl px-4 py-3 rounded-2xl bg-gray-700/50 text-gray-200';

            bubble.innerHTML = content.replace(/\n/g, '<br>');
            messageDiv.appendChild(bubble);
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function addTypingIndicator() {
            const typingDiv = document.createElement('div');
            typingDiv.id = 'typingIndicator';
            typingDiv.className = 'flex justify-start';
            typingDiv.innerHTML = `
                    <div class="max-w-2xl px-4 py-3 rounded-2xl bg-gray-700/50 text-gray-400 flex items-center gap-2">
                        <div class="flex gap-1">
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                        </div>
                        <span>AI is thinking...</span>
                    </div>
                `;
            chatMessages.appendChild(typingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function removeTypingIndicator() {
            const indicator = document.getElementById('typingIndicator');
            if (indicator) indicator.remove();
        }

        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = userInput.value.trim();
            if (!message) return;

            // Add user message
            addMessage('user', message);
            userInput.value = '';
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

            addTypingIndicator();

            try {
                const response = await fetch('{{ route("superadmin.ai-config.playground.chat") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ message })
                });

                const data = await response.json();
                removeTypingIndicator();

                if (data.success) {
                    addMessage('assistant', data.response);
                } else {
                    addMessage('assistant', '❌ Error: ' + (data.error || 'Unknown error occurred'));
                }
            } catch (error) {
                removeTypingIndicator();
                addMessage('assistant', '❌ Error: ' + error.message);
            }

            sendBtn.disabled = false;
            sendBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> Send';
        });

        // Focus input on load
        userInput.focus();
    </script>
@endsection