@extends('superadmin.layouts.app')

@section('title', 'Chats - ' . $admin->name)

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('superadmin.admins.show', $admin) }}"
                    class="p-2 rounded-lg bg-white/10 text-gray-400 hover:text-white hover:bg-white/20 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-white">💬 Chats</h1>
                    <p class="text-gray-400">{{ $admin->name }}</p>
                </div>
            </div>
            <form action="{{ route('superadmin.admins.chat-clear-all', $admin) }}" method="POST"
                onsubmit="return confirm('Are you sure you want to delete ALL chats for this admin? This cannot be undone!')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="px-4 py-2 bg-red-500/20 text-red-400 hover:bg-red-500/30 rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Clear All Chats
                </button>
            </form>
        </div>

        @if(session('success'))
            <div class="bg-green-500/20 border border-green-500/30 text-green-400 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-500/20 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <!-- Customers List (WhatsApp style) -->
        <div class="bg-dark-200 rounded-2xl border border-white/10 overflow-hidden">
            @if($customers->count() > 0)
                <div class="divide-y divide-white/5">
                    @foreach($customers as $customer)
                        <a href="{{ route('superadmin.admins.chat-view', [$admin, $customer->id]) }}"
                            class="flex items-center gap-4 p-4 hover:bg-white/5 transition-colors">
                            <!-- Avatar -->
                            <div
                                class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-500 to-purple-500 flex items-center justify-center text-white font-bold text-lg">
                                {{ strtoupper(substr($customer->name ?? $customer->phone, 0, 1)) }}
                            </div>

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-white font-medium truncate">
                                        {{ $customer->name ?? 'Unknown' }}
                                    </h3>
                                    <span class="text-xs text-gray-500">
                                        {{ $customer->updated_at->diffForHumans() }}
                                    </span>
                                </div>
                                <p class="text-gray-400 text-sm truncate">{{ $customer->phone }}</p>
                            </div>

                            <!-- Message count badge -->
                            @if($customer->chat_messages_count > 0)
                                <div class="px-2 py-1 bg-primary-500/20 text-primary-400 text-xs font-medium rounded-full">
                                    {{ $customer->chat_messages_count }} messages
                                </div>
                            @endif

                            <!-- Clear button -->
                            <form action="{{ route('superadmin.admins.chat-clear-customer', [$admin, $customer->id]) }}"
                                method="POST" onclick="event.stopPropagation();"
                                onsubmit="return confirm('Clear all chats for this customer?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="p-2 text-gray-500 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors"
                                    title="Clear chats">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </a>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="p-4 border-t border-white/5">
                    {{ $customers->links() }}
                </div>
            @else
                <div class="p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-white/5 flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-white mb-2">No chats yet</h3>
                    <p class="text-gray-400">This admin has no chat conversations</p>
                </div>
            @endif
        </div>
    </div>
@endsection