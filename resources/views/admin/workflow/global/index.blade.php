@extends('admin.layouts.app')

@section('title', 'Global Questions')

@section('content')
    <div class="p-4 lg:p-6">
        <!-- Header -->
        <div class="glass rounded-2xl p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-500 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        Global Questions
                    </h1>
                    <p class="text-gray-400 mt-1">Questions asked once per customer (City, Purpose, etc.)</p>
                </div>
                <a href="{{ route('admin.workflow.global.create') }}"
                    class="btn-primary px-6 py-3 rounded-xl text-white font-medium flex items-center gap-2 w-fit">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Question
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

        <!-- Questions Grid -->
        @if($questions->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                @foreach($questions as $question)
                    <div class="glass-light rounded-2xl p-5 hover:bg-white/10 transition-all stat-card">
                        <div class="flex justify-between items-start mb-4">
                            <div
                                class="w-12 h-12 rounded-xl flex items-center justify-center
                                                    @if($question->question_type == 'select') bg-green-500/20 @else bg-blue-500/20 @endif">
                                @if($question->question_type == 'select')
                                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                @endif
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.workflow.global.edit', $question) }}"
                                    class="w-8 h-8 rounded-lg bg-blue-500/20 hover:bg-blue-500/30 flex items-center justify-center transition-colors">
                                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                <form action="{{ route('admin.workflow.global.destroy', $question) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Delete?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="w-8 h-8 rounded-lg bg-red-500/20 hover:bg-red-500/30 flex items-center justify-center transition-colors">
                                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold text-white mb-1">{{ $question->display_name }}</h3>
                        <span
                            class="px-2 py-1 rounded-lg bg-white/5 text-gray-400 font-mono text-xs">{{ $question->question_name }}</span>

                        <div class="flex flex-wrap gap-2 mt-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium
                                                    @if($question->trigger_position == 'before_fields') bg-blue-500/20 text-blue-400
                                                    @else bg-green-500/20 text-green-400
                                                    @endif">
                                {{ $question->trigger_position == 'before_fields' ? '↑ Before Fields' : '↓ After Fields' }}
                            </span>
                            @if($question->trigger_field)
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-purple-500/20 text-purple-400">
                                    After: {{ $question->trigger_field }}
                                </span>
                            @endif
                        </div>

                        @if($question->question_type == 'select' && $question->options)
                            <div class="flex flex-wrap gap-1 mt-3">
                                @foreach(array_slice($question->options ?? [], 0, 4) as $option)
                                    <span class="px-2 py-0.5 rounded bg-white/5 text-gray-400 text-xs">{{ $option }}</span>
                                @endforeach
                                @if(count($question->options ?? []) > 4)
                                    <span
                                        class="px-2 py-0.5 rounded bg-white/10 text-gray-300 text-xs">+{{ count($question->options) - 4 }}</span>
                                @endif
                            </div>
                        @endif

                        <div class="flex justify-between items-center mt-4 pt-4 border-t border-white/10">
                            <span class="text-xs text-gray-500">Order: {{ $question->sort_order }}</span>
                            @if($question->is_active)
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400">Active</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-500/20 text-gray-400">Inactive</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="glass-light rounded-2xl p-10 text-center mb-6">
                <div class="w-16 h-16 rounded-2xl bg-cyan-500/20 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">No Global Questions</h3>
                <p class="text-gray-400 mb-6">Add questions that will be asked once per customer</p>

                <div class="flex flex-wrap justify-center gap-3 mb-6">
                    <form action="{{ route('admin.workflow.global.store') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="question_name" value="city">
                        <input type="hidden" name="display_name" value="City">
                        <input type="hidden" name="question_type" value="text">
                        <input type="hidden" name="trigger_position" value="before_fields">
                        <input type="hidden" name="is_active" value="1">
                        <button type="submit" class="glass px-4 py-3 rounded-xl hover:bg-white/10 transition-all text-left">
                            <span class="text-2xl">🏙️</span>
                            <span class="block font-medium text-white">City</span>
                            <span class="block text-xs text-gray-400">Customer location</span>
                        </button>
                    </form>
                    <form action="{{ route('admin.workflow.global.store') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="question_name" value="purpose_of_purchase">
                        <input type="hidden" name="display_name" value="Purpose of Purchase">
                        <input type="hidden" name="question_type" value="select">
                        <input type="hidden" name="options" value="Self Use,Resale,Project">
                        <input type="hidden" name="trigger_position" value="after_fields">
                        <input type="hidden" name="is_active" value="1">
                        <button type="submit" class="glass px-4 py-3 rounded-xl hover:bg-white/10 transition-all text-left">
                            <span class="text-2xl">🎯</span>
                            <span class="block font-medium text-white">Purpose</span>
                            <span class="block text-xs text-gray-400">Why buying</span>
                        </button>
                    </form>
                </div>

                <a href="{{ route('admin.workflow.global.create') }}"
                    class="btn-primary px-6 py-2 rounded-xl text-white font-medium inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Custom Question
                </a>
            </div>
        @endif

        <!-- Back Link -->
        <div class="text-center">
            <a href="{{ route('admin.workflow.fields.index') }}"
                class="inline-flex items-center gap-2 text-gray-400 hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Fields
            </a>
        </div>
    </div>
@endsection