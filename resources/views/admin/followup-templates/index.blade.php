@extends('admin.layouts.app')

@section('title', 'Followup Templates')
@section('page-title', 'Followup Templates')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-white">Followup Templates</h2>
                <p class="text-gray-400 text-sm">Automated messages sent to leads based on timing</p>
            </div>
            <a href="{{ route('admin.followup-templates.create') }}"
                class="px-4 py-2 bg-gradient-to-r from-primary-500 to-purple-600 text-white rounded-xl hover:opacity-90 transition-opacity flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Template
            </a>
        </div>

        <!-- Templates List -->
        <div class="space-y-4" id="template-list">
            @forelse($templates as $template)
                <div class="glass rounded-xl p-4" data-id="{{ $template->id }}">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-4">
                            <!-- Drag Handle -->
                            <div class="cursor-move text-gray-500 hover:text-gray-300 drag-handle pt-1">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2Zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8Zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14ZM13 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 2Zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8Zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14Z" />
                                </svg>
                            </div>

                            <!-- Order Badge -->
                            <div
                                class="w-8 h-8 rounded-lg bg-primary-500/20 text-primary-400 flex items-center justify-center font-bold">
                                {{ $template->order }}
                            </div>

                            <!-- Content -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-white font-medium">{{ $template->name }}</h3>
                                    <span class="px-2 py-1 text-xs bg-blue-500/20 text-blue-400 rounded-lg">
                                        After {{ $template->delay_minutes }} min
                                    </span>
                                    @if(!$template->is_active)
                                        <span class="px-2 py-1 text-xs bg-gray-500/20 text-gray-400 rounded-lg">Disabled</span>
                                    @endif
                                </div>
                                <p class="text-gray-400 text-sm line-clamp-2">{{ $template->message_template }}</p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2">
                            <!-- Toggle -->
                            <form action="{{ route('admin.followup-templates.toggle', $template) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="p-2 {{ $template->is_active ? 'text-green-400 hover:text-green-300' : 'text-gray-500 hover:text-gray-400' }} transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($template->is_active)
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        @endif
                                    </svg>
                                </button>
                            </form>

                            <!-- Edit -->
                            <a href="{{ route('admin.followup-templates.edit', $template) }}"
                                class="p-2 text-gray-400 hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>

                            <!-- Duplicate -->
                            <form action="{{ route('admin.followup-templates.duplicate', $template) }}" method="POST">
                                @csrf
                                <button type="submit" class="p-2 text-gray-400 hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </form>

                            <!-- Delete -->
                            <form action="{{ route('admin.followup-templates.destroy', $template) }}" method="POST"
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
                        </div>
                    </div>
                </div>
            @empty
                <div class="glass rounded-xl p-8 text-center">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    <p class="text-gray-400 mb-4">No followup templates yet</p>
                    <a href="{{ route('admin.followup-templates.create') }}"
                        class="btn-primary px-4 py-2 rounded-xl text-white">Create First Template</a>
                </div>
            @endforelse
        </div>

        <!-- Available Fields Reference -->
        <div class="glass rounded-xl p-4">
            <h3 class="text-white font-medium mb-3">Available Placeholders</h3>
            <div class="flex flex-wrap gap-2">
                <code class="px-2 py-1 bg-dark-800 text-primary-400 rounded text-sm">{customer_name}</code>
                <code class="px-2 py-1 bg-dark-800 text-primary-400 rounded text-sm">{customer_phone}</code>
                <code class="px-2 py-1 bg-dark-800 text-primary-400 rounded text-sm">{lead_stage}</code>
                <span class="text-gray-500 text-sm">+ all questionnaire field names</span>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
        <script>
            new Sortable(document.getElementById('template-list'), {
                handle: '.drag-handle',
                animation: 150,
                onEnd: function (evt) {
                    const items = [...document.querySelectorAll('#template-list [data-id]')].map(el => el.dataset.id);
                    fetch('{{ route("admin.followup-templates.reorder") }}', {
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