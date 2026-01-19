@extends('admin.layouts.app')

@section('title', 'Users')
@section('page-title', 'WhatsApp Users')

@section('content')
    <div class="space-y-6">
        <!-- Filters -->
        <div class="glass rounded-xl p-4">
            <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-wrap gap-4 items-center">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by name or number..."
                    class="input-dark flex-1 min-w-[200px] px-4 py-2 rounded-xl text-white placeholder-gray-500">
                <select name="bot_status" class="input-dark px-4 py-2 rounded-xl text-white">
                    <option value="">All Bot Status</option>
                    <option value="active" {{ request('bot_status') === 'active' ? 'selected' : '' }}>Bot Active</option>
                    <option value="inactive" {{ request('bot_status') === 'inactive' ? 'selected' : '' }}>Bot Inactive
                    </option>
                </select>
                <button type="submit" class="btn-primary px-6 py-2 rounded-xl text-white font-medium">Filter</button>
                <a href="{{ route('admin.users.index') }}"
                    class="px-4 py-2 text-gray-400 hover:text-white transition-colors">Reset</a>
            </form>
        </div>

        <!-- Users Table -->
        <div class="glass rounded-2xl overflow-hidden">
            <table class="w-full">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">User</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Lead</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Bot Status</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Last Activity</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($users ?? [] as $user)
                        @php
                            $botActive = $user->bot_enabled && !$user->bot_stopped_by_user;
                            $latestLead = $user->leads->first();
                        @endphp
                        <tr class="table-row">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-teal-500 flex items-center justify-center">
                                        <span class="text-white font-medium">{{ substr($user->name ?? 'U', 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-white font-medium">{{ $user->name ?? 'Unknown' }}</p>
                                        <p class="text-gray-400 text-sm">{{ $user->phone ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($latestLead)
                                    <span class="px-3 py-1 text-xs font-medium rounded-lg bg-primary-500/20 text-primary-400">
                                        {{ ucfirst($latestLead->stage) }}
                                    </span>
                                @else
                                    <span class="text-gray-500">No lead</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="flex items-center text-sm {{ $botActive ? 'text-green-400' : 'text-gray-500' }}">
                                    <span
                                        class="w-2 h-2 rounded-full mr-2 {{ $botActive ? 'bg-green-400 animate-pulse' : 'bg-gray-500' }}"></span>
                                    {{ $botActive ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-400">
                                {{ $user->last_activity_at ? $user->last_activity_at->diffForHumans() : 'Never' }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('admin.chats.show', $user) }}"
                                        class="p-2 text-gray-400 hover:text-primary-400 transition-colors" title="View Chat">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.users.toggle-bot', $user) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="p-2 {{ $botActive ? 'text-green-400 hover:text-red-400' : 'text-gray-400 hover:text-green-400' }} transition-colors"
                                            title="Toggle Bot">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197" />
                                </svg>
                                <p class="text-lg font-medium">No users found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if(isset($users) && $users->hasPages())
                <div class="p-4 border-t border-white/5">
                    {{ $users->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection