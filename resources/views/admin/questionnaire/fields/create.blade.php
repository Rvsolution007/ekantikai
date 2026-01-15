@extends('admin.layouts.app')

@section('title', 'Add Questionnaire Field')

@section('content')
    <div class="p-4 lg:p-6">
        <!-- Header -->
        <div class="glass rounded-2xl p-6 mb-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.questionnaire.fields.index') }}"
                    class="w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-white">Add New Field</h1>
                    <p class="text-gray-400">Create a new questionnaire field</p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.questionnaire.fields.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Form -->
                <div class="lg:col-span-2">
                    <div class="glass rounded-2xl overflow-hidden">
                        <div class="p-4 border-b border-white/10">
                            <h2 class="font-semibold text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Field Details
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Field Name <span
                                            class="text-red-400">*</span></label>
                                    <input type="text" name="field_name" value="{{ old('field_name') }}"
                                        class="w-full bg-dark-300 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors"
                                        placeholder="e.g., category, model, size" required>
                                    <p class="text-gray-500 text-sm mt-2">System identifier (lowercase, no spaces)</p>
                                    @error('field_name')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Display Name <span
                                            class="text-red-400">*</span></label>
                                    <input type="text" name="display_name" value="{{ old('display_name') }}"
                                        class="w-full bg-dark-300 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors"
                                        placeholder="e.g., Product Category" required>
                                    @error('display_name')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Field Type <span
                                            class="text-red-400">*</span></label>
                                    <select name="field_type"
                                        class="w-full bg-dark-300 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors"
                                        required>
                                        <option value="text" {{ old('field_type') == 'text' ? 'selected' : '' }}>Text (Free
                                            input)</option>
                                        <option value="select" {{ old('field_type') == 'select' ? 'selected' : '' }}>Select
                                            (From options)</option>
                                        <option value="number" {{ old('field_type') == 'number' ? 'selected' : '' }}>Number
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Options Source</label>
                                    <select name="options_source"
                                        class="w-full bg-dark-300 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors">
                                        <option value="none">None</option>
                                        <option value="catalogue" {{ old('options_source') == 'catalogue' ? 'selected' : '' }}>From Catalogue</option>
                                        <option value="manual" {{ old('options_source') == 'manual' ? 'selected' : '' }}>
                                            Manual Entry</option>
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Catalogue Column</label>
                                    <input type="text" name="catalogue_column" value="{{ old('catalogue_column') }}"
                                        class="w-full bg-dark-300 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors"
                                        placeholder="e.g., category, model_code">
                                    <p class="text-gray-500 text-sm mt-2">Column name in catalogue table</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <div class="glass rounded-2xl overflow-hidden">
                        <div class="p-4 border-b border-white/10">
                            <h2 class="font-semibold text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Settings
                            </h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="is_required" {{ old('is_required', true) ? 'checked' : '' }}
                                    class="w-5 h-5 rounded bg-dark-300 border-white/20 text-primary-500 focus:ring-primary-500 focus:ring-offset-0">
                                <span class="text-gray-300 group-hover:text-white transition-colors">Required Field</span>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="is_unique_key" {{ old('is_unique_key') ? 'checked' : '' }}
                                    class="w-5 h-5 rounded bg-dark-300 border-white/20 text-primary-500 focus:ring-primary-500 focus:ring-offset-0">
                                <span class="text-gray-300 group-hover:text-white transition-colors">🔑 Part of Unique
                                    Key</span>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="is_active" {{ old('is_active', true) ? 'checked' : '' }}
                                    class="w-5 h-5 rounded bg-dark-300 border-white/20 text-primary-500 focus:ring-primary-500 focus:ring-offset-0">
                                <span class="text-gray-300 group-hover:text-white transition-colors">Active</span>
                            </label>

                            <hr class="border-white/10">

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">AI Extraction Hints</label>
                                <textarea name="ai_extraction_hints" rows="3"
                                    class="w-full bg-dark-300 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors resize-none"
                                    placeholder="Hints for AI...">{{ old('ai_extraction_hints') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3">
                        <button type="submit"
                            class="btn-primary py-3 rounded-xl text-white font-medium flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Create Field
                        </button>
                        <a href="{{ route('admin.questionnaire.fields.index') }}"
                            class="py-3 rounded-xl text-gray-400 hover:text-white font-medium text-center bg-white/5 hover:bg-white/10 transition-colors">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection