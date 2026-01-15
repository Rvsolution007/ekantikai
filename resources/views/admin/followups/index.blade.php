@extends('admin.layouts.app')

@section('title', 'Followups')
@section('page-title', 'Followup Management')

@section('content')
    <div class="space-y-6">
        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="stat-card glass rounded-xl p-5">
                <div class="flex items-center space-x-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-500 to-orange-500 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-yellow-400">{{ $stats['pending'] ?? 0 }}</p>
                        <p class="text-gray-400">Pending</p>
                    </div>
                </div>
            </div>
            <div class="stat-card glass rounded-xl p-5">
                <div class="flex items-center space-x-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-blue-400">{{ $stats['today'] ?? 0 }}</p>
                        <p class="text-gray-400">Due Today</p>
                    </div>
                </div>
            </div>
            <div class="stat-card glass rounded-xl p-5">
                <div class="flex items-center space-x-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-500 to-rose-500 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-red-400">{{ $stats['overdue'] ?? 0 }}</p>
                        <p class="text-gray-400">Overdue</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="glass rounded-xl p-4">
            <form action="{{ route('admin.followups.index') }}" method="GET" class="flex flex-wrap gap-4 items-center">
                <select name="status" class="input-dark px-4 py-2 rounded-xl text-white">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
                <label class="flex items-center space-x-2 text-gray-300">
                    <input type="checkbox" name="due_today" value="1" {{ request('due_today') ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-gray-600 bg-dark-200 text-primary-500 focus:ring-primary-500">
                    <span>Due Today</span>
                </label>
                <label class="flex items-center space-x-2 text-gray-300">
                    <input type="checkbox" name="overdue" value="1" {{ request('overdue') ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-gray-600 bg-dark-200 text-primary-500 focus:ring-primary-500">
                    <span>Overdue Only</span>
                </label>
                <button type="submit" class="btn-primary px-6 py-2 rounded-xl text-white font-medium">Filter</button>
            </form>
        </div>

        <!-- Followups Table -->
        <div class="glass rounded-2xl overflow-hidden">
            <table class="w-full">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">User</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Lead Stage</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Last Activity</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Next Followup</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($followups ?? [] as $followup)
                        <tr class="table-row">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center">
                                        <span
                                            class="text-white font-medium">{{ substr($followup->whatsappUser->name ?? 'U', 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-white font-medium">{{ $followup->whatsappUser->name ?? 'Unknown' }}</p>
                                        <p class="text-gray-400 text-sm">{{ $followup->whatsappUser->phone ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($followup->lead)
                                    <span class="px-3 py-1 text-xs font-medium rounded-lg
                                        {{ $followup->lead->stage === 'new' ? 'bg-green-500/20 text-green-400' : '' }}
                                        {{ $followup->lead->stage === 'qualified' ? 'bg-yellow-500/20 text-yellow-400' : '' }}
                                        {{ $followup->lead->stage === 'confirmed' ? 'bg-purple-500/20 text-purple-400' : '' }}">
                                        {{ ucfirst($followup->lead->stage) }}
                                    </span>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-3 py-1 text-xs font-medium rounded-lg {{ $followup->status === 'completed' ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400' }}">
                                    {{ ucfirst($followup->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-400">
                                {{ $followup->last_activity_at ? $followup->last_activity_at->diffForHumans() : '-' }}</td>
                            <td class="px-6 py-4">
                                @if($followup->next_followup_at)
                                    <span
                                        class="{{ $followup->next_followup_at->isPast() ? 'text-red-400' : ($followup->next_followup_at->isToday() ? 'text-blue-400' : 'text-gray-300') }}">
                                        {{ $followup->next_followup_at->format('d M Y, h:i A') }}
                                    </span>
                                @else
                                    <span class="text-gray-500">Not scheduled</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    @if($followup->status !== 'completed')
                                        <form action="{{ route('admin.followups.complete', $followup) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            <button type="submit" class="p-2 text-gray-400 hover:text-green-400 transition-colors"
                                                title="Mark Complete">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.chats.show', $followup->whatsappUser) }}"
                                        class="p-2 text-gray-400 hover:text-primary-400 transition-colors" title="View Chat">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="text-lg font-medium">No followups found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if(isset($followups) && $followups->hasPages())
                <div class="p-4 border-t border-white/5">
                    {{ $followups->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection