@extends('admin.layouts.app')

@section('title', 'Workflow Fields')

@section('content')
    <div class="p-4 lg:p-6">
        <!-- Header -->
        <div class="glass rounded-2xl p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-purple-500 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        Workflow Fields
                    </h1>
                    <p class="text-gray-400 mt-1">Configure fields for the product inquiry flow</p>
                </div>
                <a href="{{ route('admin.workflow.fields.create') }}"
                    class="btn-primary px-6 py-3 rounded-xl text-white font-medium flex items-center gap-2 w-fit">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Field
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="glass-light rounded-xl p-4 mb-6 border-l-4 border-green-500 flex items-center gap-3">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-green-400">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Stats Row -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="glass-light rounded-xl p-4 stat-card">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-primary-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $fields->count() }}</div>
                        <div class="text-sm text-gray-400">Total Fields</div>
                    </div>
                </div>
            </div>
            <div class="glass-light rounded-xl p-4 stat-card">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-yellow-500/20 flex items-center justify-center">
                        <span class="text-xl">🔑</span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $fields->where('is_unique_key', true)->count() }}
                        </div>
                        <div class="text-sm text-gray-400">Unique Keys</div>
                    </div>
                </div>
            </div>
            <div class="glass-light rounded-xl p-4 stat-card">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center">
                        <span class="text-xl">🏷️</span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $fields->where('is_unique_field', true)->count() }}
                        </div>
                        <div class="text-sm text-gray-400">Unique Fields</div>
                    </div>
                </div>
            </div>
            <div class="glass-light rounded-xl p-4 stat-card">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-cyan-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $fields->where('is_active', true)->count() }}</div>
                        <div class="text-sm text-gray-400">Active</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fields Table -->
        <div class="glass rounded-2xl overflow-hidden mb-6">
            <div class="p-4 border-b border-white/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h2 class="font-semibold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Product Question
                </h2>
                <span class="px-3 py-1 rounded-lg bg-yellow-500/20 text-yellow-400 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                    </svg>
                    Drag to reorder
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider w-12">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                Field Name</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">
                                Display Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                Type</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">
                                Unique Field</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                Unique Key</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">
                                Status</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sortableFields" class="divide-y divide-white/5">
                        @forelse($fields as $index => $field)
                            <tr data-id="{{ $field->id }}" class="hover:bg-white/5 transition-colors">
                                <td class="px-4 py-4">
                                    <div
                                        class="handle cursor-move w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center hover:bg-white/10 transition-colors">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 8h16M4 16h16" />
                                        </svg>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span
                                        class="px-3 py-1.5 rounded-lg bg-primary-500/20 text-primary-300 font-mono text-sm">{{ $field->field_name }}</span>
                                    <div class="md:hidden text-sm text-gray-400 mt-1">{{ $field->display_name }}</div>
                                </td>
                                <td class="px-4 py-4 hidden md:table-cell">
                                    <span class="text-white">{{ $field->display_name }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium
                                                                                                    @if($field->field_type == 'text') bg-blue-500/20 text-blue-400
                                                                                                    @elseif($field->field_type == 'select') bg-green-500/20 text-green-400
                                                                                                    @else bg-yellow-500/20 text-yellow-400
                                                                                                    @endif">
                                        {{ ucfirst($field->field_type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center hidden lg:table-cell">
                                    <form action="{{ route('admin.workflow.fields.toggle-unique-field', $field) }}"
                                        method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="w-10 h-10 rounded-xl transition-all flex items-center justify-center mx-auto
                                                                                                        @if($field->is_unique_field) 
                                                                                                            bg-gradient-to-br from-green-500 to-teal-500 shadow-lg shadow-green-500/25
                                                                                                        @else 
                                                                                                            bg-white/5 hover:bg-white/10 border border-white/10
                                                                                                        @endif">
                                            @if($field->is_unique_field)
                                                <span class="text-white font-bold text-sm">🏷️</span>
                                            @else
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M20 12H4" />
                                                </svg>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <form action="{{ route('admin.workflow.fields.toggle-unique', $field) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        <button type="submit" class="w-10 h-10 rounded-xl transition-all flex items-center justify-center mx-auto
                                                                                                        @if($field->is_unique_key) 
                                                                                                            bg-gradient-to-br from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/25
                                                                                                        @else 
                                                                                                            bg-white/5 hover:bg-white/10 border border-white/10
                                                                                                        @endif">
                                            @if($field->is_unique_key)
                                                <span class="text-white font-bold text-sm">🔑{{ $field->unique_key_order }}</span>
                                            @else
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                                </svg>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-4 text-center hidden lg:table-cell">
                                    @if($field->is_active)
                                        <span
                                            class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400">Active</span>
                                    @else
                                        <span
                                            class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-500/20 text-red-400">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.workflow.fields.edit', $field) }}"
                                            class="w-9 h-9 rounded-lg bg-blue-500/20 hover:bg-blue-500/30 flex items-center justify-center transition-colors">
                                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('admin.workflow.fields.destroy', $field) }}" method="POST"
                                            class="inline" onsubmit="return confirm('Delete this field?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-9 h-9 rounded-lg bg-red-500/20 hover:bg-red-500/30 flex items-center justify-center transition-colors">
                                                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                            </svg>
                                        </div>
                                        <h3 class="text-white font-medium mb-2">No Fields Configured</h3>
                                        <p class="text-gray-400 mb-4">Start by adding your first questionnaire field</p>
                                        <a href="{{ route('admin.workflow.fields.create') }}"
                                            class="btn-primary px-4 py-2 rounded-lg text-white text-sm">
                                            Add First Field
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Unique Key Preview -->
        @if($fields->where('is_unique_key', true)->count() > 0)
            <div class="glass-light rounded-xl p-5 mb-6 border-l-4 border-yellow-500">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-xl">🔑</span>
                    <span class="font-semibold text-white">Unique Key Formula</span>
                </div>
                <div class="bg-dark-300 rounded-lg px-4 py-3 font-mono text-yellow-400 text-lg mb-2">
                    {{ $fields->where('is_unique_key', true)->sortBy('unique_key_order')->pluck('field_name')->join(' | ') }}
                </div>
                <p class="text-gray-400 text-sm">Same combination = Update existing row • Different combination = Create new row
                </p>
            </div>
        @endif


    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tbody = document.getElementById('sortableFields');
            if (tbody && tbody.children.length > 0 && tbody.children[0].dataset.id) {
                new Sortable(tbody, {
                    animation: 150,
                    handle: '.handle',
                    ghostClass: 'opacity-50',
                    onEnd: function (evt) {
                        const order = Array.from(tbody.querySelectorAll('tr'))
                            .map(tr => tr.dataset.id)
                            .filter(id => id);

                        fetch('{{ route("admin.workflow.fields.reorder") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ order: order })
                        });
                    }
                });
            }
        });
    </script>
@endsection