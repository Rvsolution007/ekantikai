@extends('admin.layouts.app')

@section('title', 'Chats')
@section('page-title', 'Chat Conversations')

@section('content')
    <div class="flex h-[calc(100vh-180px)] gap-6">
        <!-- Conversations List -->
        <div class="w-80 flex-shrink-0 glass rounded-2xl overflow-hidden flex flex-col">
            <div class="p-4 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Conversations</h3>
                @if($apiConnected ?? false)
                    <span class="flex items-center gap-1 text-xs text-green-400">
                        <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                        Live
                    </span>
                @endif
            </div>
            
            <!-- Search -->
            <div class="p-3 border-b border-white/5">
                <input type="text" id="searchChats" placeholder="Search conversations..."
                    class="input-dark w-full px-4 py-2 rounded-xl text-white placeholder-gray-500 text-sm"
                    onkeyup="filterConversations()">
            </div>

            <div class="flex-1 overflow-y-auto" id="conversationsList">
                @forelse($conversations ?? [] as $conv)
                    <a href="{{ route('admin.chats.show', $conv['phone']) }}"
                        class="chat-item block p-4 border-b border-white/5 hover:bg-white/5 transition-colors {{ isset($activeContact) && $activeContact['phone'] === $conv['phone'] ? 'bg-primary-500/10 border-l-2 border-l-primary-500' : '' }}">
                        <div class="flex items-center space-x-3">
                            @if(!empty($conv['profilePicUrl']))
                                <img src="{{ $conv['profilePicUrl'] }}" alt="" class="w-10 h-10 rounded-xl object-cover">
                            @else
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-teal-500 flex items-center justify-center flex-shrink-0">
                                    <span class="text-white font-medium">{{ strtoupper(substr($conv['name'] ?? 'U', 0, 1)) }}</span>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-white font-medium truncate contact-name">{{ $conv['name'] ?? 'Unknown' }}</p>
                                    @if(($conv['unreadCount'] ?? 0) > 0)
                                        <span class="bg-green-500 text-white text-xs rounded-full px-2 py-0.5">{{ $conv['unreadCount'] }}</span>
                                    @endif
                                </div>
                                <p class="text-gray-400 text-sm truncate">{{ Str::limit($conv['lastMessage'] ?? '', 30) }}</p>
                                <p class="text-gray-500 text-xs mt-1">
                                    @if($conv['lastMessageTime'] ?? null)
                                        {{ $conv['lastMessageTime']->diffForHumans(null, true) }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="p-8 text-center text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <p class="font-medium">No conversations yet</p>
                        <p class="text-sm mt-2">
                            @if(!($apiConnected ?? false))
                                <span class="text-yellow-400">WhatsApp not connected.</span><br>
                                <a href="{{ route('admin.settings.index') }}" class="text-primary-400 hover:underline">Go to Settings</a>
                            @else
                                Waiting for incoming messages
                            @endif
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Chat Area -->
        <div class="flex-1 glass rounded-2xl overflow-hidden flex flex-col">
            @if(isset($activeContact) && isset($messages))
                <!-- Chat Header -->
                <div class="p-4 border-b border-white/10 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-teal-500 flex items-center justify-center">
                            <span class="text-white font-medium">{{ strtoupper(substr($activeContact['name'] ?? 'U', 0, 1)) }}</span>
                        </div>
                        <div>
                            <p class="text-white font-medium">{{ $activeContact['name'] ?? 'Unknown' }}</p>
                            <p class="text-gray-400 text-sm">+{{ $activeContact['phone'] }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="px-3 py-1 text-xs rounded-lg {{ ($activeContact['bot_enabled'] ?? true) ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400' }}">
                            Bot {{ ($activeContact['bot_enabled'] ?? true) ? 'Active' : 'Paused' }}
                        </span>
                        <button onclick="refreshMessages()" class="p-2 rounded-lg hover:bg-white/5 text-gray-400 hover:text-white transition-colors" title="Refresh">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Messages -->
                <div class="flex-1 overflow-y-auto p-4 space-y-3" id="messagesContainer">
                    @forelse($messages as $message)
                        <div class="flex {{ $message['direction'] === 'outgoing' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[70%] {{ $message['direction'] === 'outgoing' ? 'bg-green-600/30' : 'bg-white/10' }} rounded-2xl overflow-hidden">
                                
                                {{-- Image Message --}}
                                @if($message['mediaType'] === 'image' && $message['mediaUrl'])
                                    <div class="relative">
                                        <img src="{{ $message['mediaUrl'] }}" alt="Image" class="max-w-full rounded-t-2xl cursor-pointer hover:opacity-90" 
                                             onclick="window.open('{{ $message['mediaUrl'] }}', '_blank')" loading="lazy">
                                    </div>
                                    @if($message['content'])
                                        <p class="text-white px-4 py-2 whitespace-pre-wrap">{{ $message['content'] }}</p>
                                    @endif
                                
                                {{-- Video Message --}}
                                @elseif($message['mediaType'] === 'video' && $message['mediaUrl'])
                                    <div class="relative">
                                        <video controls class="max-w-full rounded-t-2xl" preload="metadata">
                                            <source src="{{ $message['mediaUrl'] }}" type="{{ $message['mimetype'] ?? 'video/mp4' }}">
                                        </video>
                                    </div>
                                    @if($message['content'])
                                        <p class="text-white px-4 py-2 whitespace-pre-wrap">{{ $message['content'] }}</p>
                                    @endif
                                
                                {{-- Document Message --}}
                                @elseif($message['mediaType'] === 'document')
                                    <a href="{{ $message['mediaUrl'] }}" target="_blank" download 
                                       class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 transition-colors">
                                        <div class="w-10 h-10 bg-red-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-white font-medium truncate">{{ $message['fileName'] ?? 'Document' }}</p>
                                            <p class="text-gray-400 text-xs">{{ strtoupper(pathinfo($message['fileName'] ?? '', PATHINFO_EXTENSION)) ?: 'PDF' }}</p>
                                        </div>
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </a>
                                    @if($message['content'])
                                        <p class="text-white px-4 py-2 whitespace-pre-wrap border-t border-white/10">{{ $message['content'] }}</p>
                                    @endif
                                
                                {{-- Audio Message --}}
                                @elseif($message['mediaType'] === 'audio')
                                    <div class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-green-500/20 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                                                    <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1 h-1 bg-white/20 rounded-full">
                                                <div class="w-1/2 h-full bg-green-400 rounded-full"></div>
                                            </div>
                                            <span class="text-gray-400 text-xs">Audio</span>
                                        </div>
                                    </div>
                                
                                {{-- Sticker Message --}}
                                @elseif($message['mediaType'] === 'sticker' && $message['mediaUrl'])
                                    <div class="p-2">
                                        <img src="{{ $message['mediaUrl'] }}" alt="Sticker" class="w-32 h-32 object-contain" loading="lazy">
                                    </div>
                                
                                {{-- Text Message --}}
                                @else
                                    <p class="text-white px-4 py-3 whitespace-pre-wrap">{{ $message['content'] ?: '[Empty message]' }}</p>
                                @endif
                                
                                {{-- Timestamp --}}
                                <div class="flex items-center justify-end gap-2 px-4 pb-2 {{ $message['mediaType'] ? 'pt-1' : '' }}">
                                    <p class="text-gray-400 text-xs">{{ $message['timestamp']->format('h:i A') }}</p>
                                    @if($message['direction'] === 'outgoing')
                                        @if($message['status'] === 'READ')
                                            <svg class="w-4 h-4 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M18 7l-1.41-1.41-6.34 6.34 1.41 1.41L18 7zm4.24-1.41L11.66 16.17 7.48 12l-1.41 1.41L11.66 19l12-12-1.42-1.41zM.41 13.41L6 19l1.41-1.41L1.83 12 .41 13.41z"/>
                                            </svg>
                                        @elseif($message['status'] === 'DELIVERY_ACK')
                                            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M18 7l-1.41-1.41-6.34 6.34 1.41 1.41L18 7zm4.24-1.41L11.66 16.17 7.48 12l-1.41 1.41L11.66 19l12-12-1.42-1.41zM.41 13.41L6 19l1.41-1.41L1.83 12 .41 13.41z"/>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                            </svg>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex-1 flex items-center justify-center h-full">
                            <div class="text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <p>No messages yet</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Input -->
                <div class="p-4 border-t border-white/10">
                    <!-- File Preview -->
                    <div id="filePreview" class="hidden mb-3 p-3 bg-white/5 rounded-xl flex items-center gap-3">
                        <div id="previewContent" class="flex-1 flex items-center gap-3">
                            <img id="imagePreview" class="hidden w-16 h-16 rounded-lg object-cover">
                            <div id="docPreview" class="hidden flex items-center gap-3">
                                <div class="w-10 h-10 bg-red-500/20 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <span id="fileName" class="text-white text-sm"></span>
                            </div>
                        </div>
                        <button type="button" onclick="clearFile()" class="text-gray-400 hover:text-white p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <form id="sendMessageForm" onsubmit="sendMessage(event)" class="flex items-center space-x-3">
                        <input type="hidden" id="phoneNumber" value="{{ $activeContact['phone'] }}">
                        <input type="file" id="fileInput" class="hidden" accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx" onchange="handleFileSelect(event)">
                        
                        <!-- Attachment Button -->
                        <button type="button" onclick="document.getElementById('fileInput').click()" 
                                class="p-3 rounded-xl bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white transition-colors" title="Attach file">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                        </button>
                        
                        <input type="text" id="messageInput" placeholder="Type a message..."
                            class="input-dark flex-1 px-4 py-3 rounded-xl text-white placeholder-gray-500">
                        <button type="submit" class="btn-primary px-6 py-3 rounded-xl text-white flex items-center gap-2" id="sendBtn">
                            <span>Send</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </form>
                </div>
            @else
                <!-- Empty State -->
                <div class="flex-1 flex items-center justify-center">
                    <div class="text-center text-gray-400">
                        <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-gradient-to-br from-green-500/20 to-teal-500/20 flex items-center justify-center">
                            <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <p class="text-xl font-medium text-white mb-2">Select a conversation</p>
                        <p class="text-gray-500">Choose a contact from the left to view messages</p>
                        
                        @if(!($apiConnected ?? false))
                            <div class="mt-6 p-4 bg-yellow-500/10 rounded-xl border border-yellow-500/20">
                                <p class="text-yellow-400 text-sm mb-2">WhatsApp is not connected</p>
                                <a href="{{ route('admin.settings.index') }}" class="btn-primary px-4 py-2 rounded-lg text-white text-sm inline-block">
                                    Connect WhatsApp
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            let selectedFile = null;
            
            // Auto scroll to bottom
            function scrollToBottom() {
                const container = document.getElementById('messagesContainer');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            }
            scrollToBottom();

            // Filter conversations
            function filterConversations() {
                const search = document.getElementById('searchChats').value.toLowerCase();
                const items = document.querySelectorAll('.chat-item');
                
                items.forEach(item => {
                    const name = item.querySelector('.contact-name').textContent.toLowerCase();
                    if (name.includes(search)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            }

            // Handle file selection
            function handleFileSelect(event) {
                const file = event.target.files[0];
                if (!file) return;
                
                selectedFile = file;
                const preview = document.getElementById('filePreview');
                const imgPreview = document.getElementById('imagePreview');
                const docPreview = document.getElementById('docPreview');
                const fileName = document.getElementById('fileName');
                
                preview.classList.remove('hidden');
                
                if (file.type.startsWith('image/')) {
                    imgPreview.classList.remove('hidden');
                    docPreview.classList.add('hidden');
                    imgPreview.src = URL.createObjectURL(file);
                } else {
                    imgPreview.classList.add('hidden');
                    docPreview.classList.remove('hidden');
                    fileName.textContent = file.name;
                }
            }

            // Clear selected file
            function clearFile() {
                selectedFile = null;
                document.getElementById('fileInput').value = '';
                document.getElementById('filePreview').classList.add('hidden');
                document.getElementById('imagePreview').classList.add('hidden');
                document.getElementById('docPreview').classList.add('hidden');
            }

            // Send message (text or media)
            async function sendMessage(e) {
                e.preventDefault();
                
                const phone = document.getElementById('phoneNumber').value;
                const message = document.getElementById('messageInput').value.trim();
                const sendBtn = document.getElementById('sendBtn');
                
                // If no file and no message, return
                if (!selectedFile && !message) return;
                
                sendBtn.disabled = true;
                sendBtn.innerHTML = '<span>Sending...</span>';
                
                try {
                    let response, data;
                    
                    if (selectedFile) {
                        // Send file
                        const formData = new FormData();
                        formData.append('phone', phone);
                        formData.append('file', selectedFile);
                        if (message) formData.append('caption', message);
                        
                        response = await fetch('{{ route("admin.chats.send-media") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData
                        });
                    } else {
                        // Send text
                        response = await fetch('{{ route("admin.chats.send") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ phone, message })
                        });
                    }
                    
                    data = await response.json();
                    
                    if (data.success) {
                        // Add message to UI
                        const container = document.getElementById('messagesContainer');
                        const msgDiv = document.createElement('div');
                        msgDiv.className = 'flex justify-end';
                        
                        let content = '';
                        if (selectedFile && selectedFile.type.startsWith('image/')) {
                            content = `<img src="${URL.createObjectURL(selectedFile)}" class="max-w-full rounded-t-2xl">`;
                            if (message) content += `<p class="text-white px-4 py-2 whitespace-pre-wrap">${message}</p>`;
                        } else if (selectedFile) {
                            content = `<div class="px-4 py-3"><span class="text-white">📎 ${selectedFile.name}</span></div>`;
                        } else {
                            content = `<p class="text-white px-4 py-3 whitespace-pre-wrap">${message}</p>`;
                        }
                        
                        msgDiv.innerHTML = `
                            <div class="max-w-[70%] bg-green-600/30 rounded-2xl overflow-hidden">
                                ${content}
                                <div class="flex items-center justify-end gap-2 px-4 pb-2">
                                    <p class="text-gray-400 text-xs">Just now</p>
                                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                    </svg>
                                </div>
                            </div>
                        `;
                        container.appendChild(msgDiv);
                        
                        document.getElementById('messageInput').value = '';
                        clearFile();
                        scrollToBottom();
                    } else {
                        alert('Failed to send: ' + (data.error || 'Unknown error'));
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                } finally {
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = '<span>Send</span><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>';
                }
            }

            // Refresh messages
            function refreshMessages() {
                location.reload();
            }

            // Auto-refresh every 30 seconds
            @if(isset($activeContact))
            setInterval(() => {
                // In production, you'd use AJAX to fetch new messages
            }, 30000);
            @endif
        </script>
    @endpush
@endsection