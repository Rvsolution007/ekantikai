@extends('admin.layouts.app')

@section('title', 'Edit Lead Status')
@section('page-title', 'Edit Lead Status')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="glass rounded-2xl p-6">
            <form action="{{ route('admin.lead-status.update', $leadStatus) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Status Name</label>
                    <input type="text" name="name" value="{{ old('name', $leadStatus->name) }}" required
                           class="w-full input-dark px-4 py-3 rounded-xl text-white"
                           placeholder="e.g., Qualified, Quoted, Won"
                           {{ $leadStatus->is_default ? 'readonly' : '' }}>
                    @if($leadStatus->is_default)
                        <p class="text-yellow-400 text-xs mt-1">Default status name cannot be changed</p>
                    @endif
                    @error('name')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Color -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Status Color</label>
                    <div class="flex items-center gap-4">
                        <input type="color" name="color" value="{{ old('color', $leadStatus->color) }}"
                               class="w-16 h-12 rounded-lg cursor-pointer border-0">
                        <div class="flex gap-2">
                            @foreach(['#22c55e', '#eab308', '#f97316', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899'] as $preset)
                                <button type="button" onclick="document.querySelector('input[name=color]').value = '{{ $preset }}'"
                                        class="w-8 h-8 rounded-lg border-2 border-white/20 hover:border-white/50 transition-colors"
                                        style="background-color: {{ $preset }}"></button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Active Toggle -->
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" 
                               {{ old('is_active', $leadStatus->is_active) ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-gray-600 text-primary-500 focus:ring-primary-500">
                        <span class="text-gray-300">Active (show in Kanban board)</span>
                    </label>
                </div>

                <!-- Connected Questions -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Connected Questions</label>
                    <p class="text-gray-500 text-xs mb-3">AI will auto-assign leads to this status when these questions are answered</p>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach($questions ?? [] as $question)
                            <label class="flex items-center gap-3 p-3 bg-dark-800/50 rounded-xl cursor-pointer hover:bg-dark-800 transition-colors">
                                <input type="checkbox" name="connected_question_ids[]" value="{{ $question->id }}"
                                       {{ in_array($question->id, $leadStatus->connected_question_ids ?? []) ? 'checked' : '' }}
                                       class="w-5 h-5 rounded border-gray-600 text-primary-500 focus:ring-primary-500">
                                <span class="text-gray-300">{{ $question->questionnaireField->display_name ?? $question->config['label'] ?? 'Question #' . $question->id }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-4 border-t border-white/10">
                    <a href="{{ route('admin.lead-status.index') }}" 
                       class="px-6 py-3 text-gray-400 hover:text-white transition-colors">Cancel</a>
                    <button type="submit" 
                            class="px-6 py-3 bg-gradient-to-r from-primary-500 to-purple-600 text-white rounded-xl hover:opacity-90 transition-opacity">
                        Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
