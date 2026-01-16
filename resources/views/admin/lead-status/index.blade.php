@extends('admin.layouts.app')

@section('title', 'Lead Statuses')
@section('page-title', 'Lead Status Management')

@section('content')
    <div class="space-y-6">
        <!-- Header Actions -->
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-white">Lead Statuses</h2>
                <p class="text-gray-400 text-sm">Manage custom lead statuses for your pipeline</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.lead-status.kanban') }}"
                    class="px-4 py-2 bg-purple-500/20 text-purple-400 rounded-xl hover:bg-purple-500/30 transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                    </svg>
                    Kanban View
                </a>
                <a href="{{ route('admin.lead-status.create') }}"
                    class="px-4 py-2 bg-gradient-to-r from-primary-500 to-purple-600 text-white rounded-xl hover:opacity-90 transition-opacity flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Status
                </a>
            </div>
        </div>

        <!-- Status Cards -->
        <div class="grid gap-4" id="status-list">
            @forelse($statuses as $status)
                <div class="glass rounded-xl p-4 flex items-center justify-between" data-id="{{ $status->id }}">
                    <div class="flex items-center gap-4">
                        <!-- Drag Handle -->
                        <div class="cursor-move text-gray-500 hover:text-gray-300 drag-handle">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2Zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8Zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14ZM13 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 2Zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8Zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14Z" />
                            </svg>
                        </div>

                        <!-- Color Badge -->
                        <div class="w-4 h-4 rounded-full" style="background-color: {{ $status->color }}"></div>

                        <!-- Name -->
                        <div>
                            <h3 class="text-white font-medium">{{ $status->name }}</h3>
                            <p class="text-gray-400 text-sm">{{ $status->leads_count }} leads</p>
                        </div>

                        @if($status->is_default)
                            <span class="px-2 py-1 text-xs bg-blue-500/20 text-blue-400 rounded-lg">Default</span>
                        @endif
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Edit -->
                        <a href="{{ route('admin.lead-status.edit', $status) }}"
                            class="p-2 text-gray-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </a>

                        <!-- Delete -->
                        @if(!$status->is_default)
                            <form action="{{ route('admin.lead-status.destroy', $status) }}" method="POST"
                                onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-red-400 hover:text-red-300 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="glass rounded-xl p-8 text-center">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p class="text-gray-400 mb-4">No custom statuses yet</p>
                    <a href="{{ route('admin.lead-status.create') }}" class="btn-primary px-4 py-2 rounded-xl text-white">Create
                        First Status</a>
                </div>
            @endforelse
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
        <script>
            new Sortable(document.getElementById('status-list'), {
                handle: '.drag-handle',
                animation: 150,
                onEnd: function (evt) {
                    const items = [...document.querySelectorAll('#status-list [data-id]')].map(el => el.dataset.id);
                    fetch('{{ route("admin.lead-status.reorder") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ order: items })
                    });
                }
            });
        </script>
    @endpush
@endsection