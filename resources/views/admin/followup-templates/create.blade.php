@extends('admin.layouts.app')

@section('title', 'Create Followup Template')
@section('page-title', 'Create Followup Template')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="glass rounded-2xl p-6">
            <form action="{{ route('admin.followup-templates.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Template Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full input-dark px-4 py-3 rounded-xl text-white"
                        placeholder="e.g., First Reminder, 24 Hour Follow-up">
                    @error('name')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Delay -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Send After (minutes)</label>
                    <div class="flex items-center gap-4">
                        <input type="number" name="delay_minutes" value="{{ old('delay_minutes', 60) }}" min="1" max="10080"
                            required class="w-32 input-dark px-4 py-3 rounded-xl text-white">
                        <div class="flex gap-2">
                            <button type="button" onclick="document.querySelector('input[name=delay_minutes]').value = 30"
                                class="px-3 py-2 bg-gray-600/20 text-gray-400 rounded-lg hover:bg-gray-600/40 text-sm">30m</button>
                            <button type="button" onclick="document.querySelector('input[name=delay_minutes]').value = 60"
                                class="px-3 py-2 bg-gray-600/20 text-gray-400 rounded-lg hover:bg-gray-600/40 text-sm">1h</button>
                            <button type="button" onclick="document.querySelector('input[name=delay_minutes]').value = 1440"
                                class="px-3 py-2 bg-gray-600/20 text-gray-400 rounded-lg hover:bg-gray-600/40 text-sm">24h</button>
                            <button type="button" onclick="document.querySelector('input[name=delay_minutes]').value = 4320"
                                class="px-3 py-2 bg-gray-600/20 text-gray-400 rounded-lg hover:bg-gray-600/40 text-sm">3d</button>
                        </div>
                    </div>
                    @error('delay_minutes')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Message Template -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Message Template</label>
                    <textarea name="message_template" rows="6" required
                        class="w-full input-dark px-4 py-3 rounded-xl text-white resize-none"
                        placeholder="Hello {customer_name}, ye reminder hai aapki enquiry ke baare mein...">{{ old('message_template') }}</textarea>
                    @error('message_template')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Available Fields -->
                <div class="bg-dark-800/50 rounded-xl p-4">
                    <h4 class="text-sm font-medium text-gray-300 mb-2">Available Placeholders</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($availableFields as $field)
                            <button type="button" onclick="insertPlaceholder('{!! $field !!}')"
                                class="px-2 py-1 bg-primary-500/20 text-primary-400 rounded text-sm hover:bg-primary-500/30 transition-colors">
                                {{ '{' . $field . '}' }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-4 border-t border-white/10">
                    <a href="{{ route('admin.followup-templates.index') }}"
                        class="px-6 py-3 text-gray-400 hover:text-white transition-colors">Cancel</a>
                    <button type="submit"
                        class="px-6 py-3 bg-gradient-to-r from-primary-500 to-purple-600 text-white rounded-xl hover:opacity-90 transition-opacity">
                        Create Template
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function insertPlaceholder(field) {
                const textarea = document.querySelector('textarea[name="message_template"]');
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const text = textarea.value;
                const placeholder = '{' + field + '}';
                textarea.value = text.substring(0, start) + placeholder + text.substring(end);
                textarea.focus();
                textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;
            }
        </script>
    @endpush
@endsection