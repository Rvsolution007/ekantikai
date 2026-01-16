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

        <!-- Kanban Board -->
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