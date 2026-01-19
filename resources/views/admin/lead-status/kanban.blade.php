@extends('admin.layouts.app')

@section('title', 'Lead Kanban')
@section('page-title', 'Lead Pipeline')

@push('styles')
    <style>
        .kanban-board {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding-bottom: 1rem;
            min-height: calc(100vh - 200px);
        }

        .kanban-column {
            min-width: 300px;
            max-width: 320px;
            flex-shrink: 0;
        }

        .kanban-cards {
            min-height: 200px;
            max-height: calc(100vh - 350px);
            overflow-y: auto;
        }

        .kanban-card {
            cursor: grab;
            transition: all 0.2s ease;
        }

        .kanban-card:active {
            cursor: grabbing;
        }

        .kanban-card.dragging {
            opacity: 0.5;
            transform: scale(1.02);
        }

        .kanban-column.drag-over {
            background: rgba(139, 92, 246, 0.1);
        }
    </style>
@endpush

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-white">Kanban Board</h2>
                <p class="text-gray-400 text-sm">Drag leads between statuses to update their stage</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.leads.index') }}"
                    class="px-4 py-2 bg-gray-500/20 text-gray-400 rounded-xl hover:bg-gray-500/30 transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    List View
                </a>
                <a href="{{ route('admin.lead-status.index') }}"
                    class="px-4 py-2 bg-purple-500/20 text-purple-400 rounded-xl hover:bg-purple-500/30 transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Manage Statuses
                </a>
            </div>
        </div>

        <!-- Search & Filter Bar -->
        <div class="glass rounded-xl p-4 relative z-20">
            <form action="{{ route('admin.lead-status.kanban') }}" method="GET" id="kanbanFilterForm">
                <div class="flex items-center gap-4">
                    <!-- Search Input -->
                    <div class="flex-1">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search by name or phone..."
                            class="input-dark w-full px-4 py-3 rounded-xl text-white placeholder-gray-500"
                            onkeypress="if(event.key==='Enter') document.getElementById('kanbanFilterForm').submit()">
                    </div>

                    <!-- Filter Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button type="button" @click="open = !open"
                            class="flex items-center gap-2 px-5 py-3 bg-gray-700/50 hover:bg-gray-600/50 border border-gray-600 rounded-xl text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filter
                            @if(request('quality') || request('bot_status'))
                                <span class="w-2 h-2 bg-primary-500 rounded-full"></span>
                            @endif
                            <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown Panel -->
                        <div x-show="open" @click.away="open = false" x-transition
                            class="absolute right-0 mt-2 w-72 bg-dark-100 border border-gray-700 rounded-xl shadow-2xl p-4 space-y-4"
                            style="z-index: 9999;">

                            <!-- Quality Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">Lead Quality</label>
                                <select name="quality" class="input-dark w-full px-4 py-2.5 rounded-lg text-white">
                                    <option value="">All Qualities</option>
                                    <option value="hot" {{ request('quality') === 'hot' ? 'selected' : '' }}>🔥 Hot</option>
                                    <option value="warm" {{ request('quality') === 'warm' ? 'selected' : '' }}>🌡️ Warm
                                    </option>
                                    <option value="cold" {{ request('quality') === 'cold' ? 'selected' : '' }}>❄️ Cold
                                    </option>
                                </select>
                            </div>

                            <!-- Bot Status Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">Bot Status</label>
                                <select name="bot_status" class="input-dark w-full px-4 py-2.5 rounded-lg text-white">
                                    <option value="">All Bot Status</option>
                                    <option value="active" {{ request('bot_status') === 'active' ? 'selected' : '' }}>🟢
                                        Active</option>
                                    <option value="inactive" {{ request('bot_status') === 'inactive' ? 'selected' : '' }}>⚪
                                        Inactive</option>
                                </select>
                            </div>

                            <!-- Filter Actions -->
                            <div class="flex gap-3 pt-2 border-t border-gray-700">
                                <button type="submit" @click="open = false"
                                    class="flex-1 btn-primary px-4 py-2.5 rounded-lg text-white font-medium text-sm">
                                    Apply Filters
                                </button>
                                <a href="{{ route('admin.lead-status.kanban') }}"
                                    class="px-4 py-2.5 bg-gray-700/50 hover:bg-gray-600/50 rounded-lg text-gray-300 text-sm font-medium transition-colors">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Filters Display -->
                @if(request('search') || request('quality') || request('bot_status'))
                    <div class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-white/5">
                        <span class="text-sm text-gray-500">Active filters:</span>
                        @if(request('search'))
                            <span class="px-2 py-1 bg-primary-500/20 text-primary-400 text-xs rounded-lg flex items-center gap-1">
                                Search: "{{ request('search') }}"
                                <a href="{{ route('admin.lead-status.kanban', array_merge(request()->except('search'))) }}"
                                    class="hover:text-white">×</a>
                            </span>
                        @endif
                        @if(request('quality'))
                            <span class="px-2 py-1 bg-orange-500/20 text-orange-400 text-xs rounded-lg flex items-center gap-1">
                                Quality: {{ ucfirst(request('quality')) }}
                                <a href="{{ route('admin.lead-status.kanban', array_merge(request()->except('quality'))) }}"
                                    class="hover:text-white">×</a>
                            </span>
                        @endif
                        @if(request('bot_status'))
                            <span class="px-2 py-1 bg-green-500/20 text-green-400 text-xs rounded-lg flex items-center gap-1">
                                Bot: {{ ucfirst(request('bot_status')) }}
                                <a href="{{ route('admin.lead-status.kanban', array_merge(request()->except('bot_status'))) }}"
                                    class="hover:text-white">×</a>
                            </span>
                        @endif
                    </div>
                @endif
            </form>
        </div>

        <div class="kanban-board">
            @foreach($statuses as $status)
                <div class="kanban-column" data-status-id="{{ $status->id }}">
                    <!-- Column Header -->
                    <div class="glass rounded-t-xl p-4 border-b border-white/10">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full" style="background-color: {{ $status->color }}"></div>
                                <h3 class="text-white font-medium">{{ $status->name }}</h3>
                            </div>
                            <span class="px-2 py-1 text-xs bg-white/10 text-gray-400 rounded-lg">
                                {{ isset($leads[$status->id]) ? count($leads[$status->id]) : 0 }}
                            </span>
                        </div>
                    </div>

                    <div class="glass rounded-b-xl p-3 kanban-cards" data-status-id="{{ $status->id }}">
                        @php $statusLeads = $leads[$status->id] ?? collect(); @endphp
                        @foreach($statusLeads as $lead)
                            <div class="kanban-card bg-dark-800/50 rounded-xl p-4 mb-3 border border-white/5 hover:border-white/20 transition-colors"
                                data-lead-id="{{ $lead->id }}" draggable="true">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center">
                                            <span
                                                class="text-white text-xs font-medium">{{ substr($lead->contact_name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <p class="text-white text-sm font-medium">{{ $lead->contact_name }}</p>
                                            <p class="text-gray-500 text-xs">{{ $lead->contact_phone }}</p>
                                        </div>
                                    </div>
                                    <span
                                        class="px-2 py-1 text-xs rounded-lg
                                                                            {{ $lead->lead_quality === 'hot' ? 'bg-red-500/20 text-red-400' : '' }}
                                                                            {{ $lead->lead_quality === 'warm' ? 'bg-orange-500/20 text-orange-400' : '' }}
                                                                            {{ $lead->lead_quality === 'cold' ? 'bg-blue-500/20 text-blue-400' : '' }}">
                                        {{ ucfirst($lead->lead_quality ?? 'new') }}
                                    </span>
                                </div>

                                @if($lead->bot_active)
                                    <span class="inline-flex items-center gap-1 text-xs text-green-400 mb-2">
                                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                                        Bot Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs text-gray-500 mb-2">
                                        <span class="w-1.5 h-1.5 bg-gray-500 rounded-full"></span>
                                        Bot Inactive
                                    </span>
                                @endif

                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <span>{{ $lead->created_at->diffForHumans() }}</span>
                                    <a href="{{ route('admin.leads.show', $lead) }}"
                                        class="text-primary-400 hover:text-primary-300">
                                        View →
                                    </a>
                                </div>
                            </div>
                        @endforeach

                        @if($statusLeads->isEmpty())
                            <div class="text-center py-8 text-gray-500">
                                <p class="text-sm">No leads</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const cards = document.querySelectorAll('.kanban-card');
                const columns = document.querySelectorAll('.kanban-cards');

                cards.forEach(card => {
                    card.addEventListener('dragstart', function (e) {
                        e.dataTransfer.setData('text/plain', this.dataset.leadId);
                        this.classList.add('dragging');
                    });

                    card.addEventListener('dragend', function () {
                        this.classList.remove('dragging');
                        columns.forEach(col => col.classList.remove('drag-over'));
                    });
                });

                columns.forEach(column => {
                    column.addEventListener('dragover', function (e) {
                        e.preventDefault();
                        this.classList.add('drag-over');
                    });

                    column.addEventListener('dragleave', function () {
                        this.classList.remove('drag-over');
                    });

                    column.addEventListener('drop', function (e) {
                        e.preventDefault();
                        this.classList.remove('drag-over');

                        const leadId = e.dataTransfer.getData('text/plain');
                        const statusId = this.dataset.statusId;
                        const card = document.querySelector(`[data-lead-id="${leadId}"]`);

                        if (card) {
                            this.appendChild(card);

                            // API call to update
                            fetch('{{ route("admin.lead-status.move-lead") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ lead_id: leadId, status_id: statusId })
                            }).then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        // Update count badges
                                        location.reload();
                                    }
                                });
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection