@extends('layouts.superadmin')

@section('title', 'Chat - ' . ($customer->name ?? $customer->phone))

@section('content')
    <div class="flex flex-col h-[calc(100vh-120px)]">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 bg-dark-200 rounded-t-2xl border-b border-white/10">
            <div class="flex items-center gap-4">
                <a href="{{ route('superadmin.admins.chats', $admin) }}"
                    class="p-2 rounded-lg bg-white/10 text-gray-400 hover:text-white hover:bg-white/20 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div
                    class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-purple-500 flex items-center justify-center text-white font-bold">
                    {{ strtoupper(substr($customer->name ?? $customer->phone, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-white font-medium">{{ $customer->name ?? 'Unknown' }}</h2>
                    <p class="text-gray-400 text-sm">{{ $customer->phone }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-gray-400 text-sm">{{ $messages->count() }} messages</span>
                <form action="{{ route('superadmin.admins.chat-clear-customer', [$admin, $customer->id]) }}" method="POST"
                    onsubmit="return confirm('Clear all chats for this customer?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-3 py-2 bg-red-500/20 text-red-400 hover:bg-red-500/30 rounded-lg transition-colors text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Clear All
                    </button>
                </form>
            </div>
        </div>

        <!-- Chat Messages (WhatsApp Style) -->
        <div class="flex-1 overflow-y-auto p-4 bg-dark-300 space-y-4" id="chat-container"
            style="background-image: url('data:image/svg+xml,<svg xmlns=\" http://www.w3.org/2000/svg\" viewBox=\"0 0 80
            80\">
            <rect fill=\"%23111827\" width=\"80\" height=\"80\" />
            <circle fill=\"%231f2937\" cx=\"40\" cy=\"40\" r=\"2\" opacity=\"0.3\" /></svg>'); background-size: 40px 40px;">

            @if($messages->count() > 0)
                @foreach($messages as $message)
                    <div class="flex {{ $message->role === 'user' ? 'justify-start' : 'justify-end' }} group">
                        <div class="max-w-xs lg:max-w-md relative">
                            <!-- Message bubble -->
                            <div class="px-4 py-2 rounded-2xl {{ $message->role === 'user'
                        ? 'bg-white/10 text-white rounded-bl-none'
                        : 'bg-gradient-to-r from-primary-500 to-purple-500 text-white rounded-br-none' }}">
                                <p class="text-sm whitespace-pre-wrap">{{ $message->content }}</p>
                                <div class="flex items-center justify-end gap-2 mt-1">
                                    <span class="text-xs {{ $message->role === 'user' ? 'text-gray-500' : 'text-white/70' }}">
                                        {{ $message->created_at->format('H:i') }}
                                    </span>
                                </div>
                            </div>

                            <!-- Delete button (on hover) -->
                            <button onclick="deleteMessage({{ $message->id }})"
                                class="absolute -right-8 top-1/2 -translate-y-1/2 p-1.5 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 opacity-0 group-hover:opacity-100 transition-opacity"
                                title="Delete message">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-white/5 flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <p class="text-gray-400">No messages in this chat</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Info Footer -->
        <div class="p-4 bg-dark-200 rounded-b-2xl border-t border-white/10">
            <div class="flex items-center justify-between text-sm text-gray-400">
                <span>Admin: {{ $admin->name }}</span>
                <span>Customer since: {{ $customer->created_at->format('M d, Y') }}</span>
            </div>
        </div>
    </div>

    <script>
        // Auto scroll to bottom
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('chat-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        });

        function deleteMessage(messageId) {
            if (!confirm('Delete this message?')) return;

            fetch(`{{ url('superadmin/admins/' . $admin->id . '/chats') }}/${messageId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to delete message');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete message');
                });
        }
    </script>
@endsection