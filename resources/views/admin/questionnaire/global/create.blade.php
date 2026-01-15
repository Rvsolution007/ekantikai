@extends('admin.layouts.app')

@section('title', 'Add Global Question')

@section('content')
    <div class="p-4 lg:p-6">
        <!-- Header -->
        <div class="glass rounded-2xl p-6 mb-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.questionnaire.global.index') }}"
                    class="w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-white">Add Global Question</h1>
                    <p class="text-gray-400">Create a question asked once per customer</p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.questionnaire.global.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <div class="glass rounded-2xl overflow-hidden">
                        <div class="p-4 border-b border-white/10">
                            <h2 class="font-semibold text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Question Details
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Field Name <span
                                            class="text-red-400">*</span></label>
                                    <input type="text" name="field_name" value="{{ old('field_name') }}"
                                        class="w-full bg-dark-300 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-colors font-mono"
                                        placeholder="e.g., city, purpose" required>
                                    @error('field_name')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
                                    <p class="text-gray-500 text-xs mt-1">Internal field identifier (no spaces)</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Display Name <span
                                            class="text-red-400">*</span></label>
                                    <input type="text" name="display_name" value="{{ old('display_name') }}"
                                        class="w-full bg-dark-300 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-colors"
                                        placeholder="e.g., City, Purpose of Purchase" required>
                                    @error('display_name')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
                                    <p class="text-gray-500 text-xs mt-1">Shows in flowchart & chatbot</p>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Question Type <span
                                            class="text-red-400">*</span></label>
                                    <select name="question_type" id="questionType"
                                        class="w-full bg-dark-300 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-colors"
                                        required>
                                        <option value="text" {{ old('question_type') == 'text' ? 'selected' : '' }}>Text (Free
                                            input)</option>
                                        <option value="select" {{ old('question_type') == 'select' ? 'selected' : '' }}>Select
                                            (From options)</option>
                                    </select>
                                </div>

                                <div class="md:col-span-2" id="optionsField" style="display: none;">
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Options (comma
                                        separated)</label>
                                    <textarea name="options" rows="2"
                                        class="w-full bg-dark-300 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-colors resize-none"
                                        placeholder="Self Use, Resale, Project, Contractor">{{ old('options') }}</textarea>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Add question</label>
                                    <input type="text" name="add_question" value="{{ old('add_question') }}"
                                        class="w-full bg-dark-300 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-colors"
                                        placeholder="Enter question text">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="glass rounded-2xl overflow-hidden">
                        <div class="p-4 border-b border-white/10">
                            <h2 class="font-semibold text-white">Settings</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="is_active" {{ old('is_active', true) ? 'checked' : '' }}
                                    class="w-5 h-5 rounded bg-dark-300 border-white/20 text-cyan-500 focus:ring-cyan-500 focus:ring-offset-0">
                                <span class="text-gray-300 group-hover:text-white transition-colors">Active</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3">
                        <button type="submit"
                            class="bg-gradient-to-r from-cyan-500 to-blue-500 py-3 rounded-xl text-white font-medium flex items-center justify-center gap-2 hover:shadow-lg hover:shadow-cyan-500/25 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Create Question
                        </button>
                        <a href="{{ route('admin.questionnaire.global.index') }}"
                            class="py-3 rounded-xl text-gray-400 hover:text-white font-medium text-center bg-white/5 hover:bg-white/10 transition-colors">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('questionType').addEventListener('change', function () {
            document.getElementById('optionsField').style.display = this.value === 'select' ? 'block' : 'none';
        });
        if (document.getElementById('questionType').value === 'select') {
            document.getElementById('optionsField').style.display = 'block';
        }
    </script>
@endsection