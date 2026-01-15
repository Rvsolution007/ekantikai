@extends('admin.layouts.app')

@section('title', 'Edit Template: ' . $fieldName)

@section('content')
    <div class="p-4 lg:p-6">
        <!-- Header -->
        <div class="glass rounded-2xl p-6 mb-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.questionnaire.templates.index') }}"
                        class="w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-white">Edit Templates</h1>
                        <p class="text-gray-400">Multi-language question texts</p>
                    </div>
                </div>
                <span class="px-4 py-2 rounded-xl bg-green-500/20 text-green-300 font-mono">{{ $fieldName }}</span>
            </div>
        </div>

        <form action="{{ route('admin.questionnaire.templates.store', $fieldName) }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                @foreach($languages as $code => $langName)
                    @php
                        $template = $templates[$code] ?? null;
                        $colors = [
                            'hi' => ['from' => 'orange-500', 'to' => 'amber-500', 'focus' => 'orange-500'],
                            'en' => ['from' => 'blue-500', 'to' => 'indigo-500', 'focus' => 'blue-500'],
                            'gu' => ['from' => 'purple-500', 'to' => 'violet-500', 'focus' => 'purple-500'],
                            'ta' => ['from' => 'pink-500', 'to' => 'rose-500', 'focus' => 'pink-500'],
                            'hinglish' => ['from' => 'teal-500', 'to' => 'cyan-500', 'focus' => 'teal-500'],
                        ];
                        $color = $colors[$code] ?? $colors['en'];
                        $flags = ['hi' => '🇮🇳', 'en' => '🇬🇧', 'gu' => '🇮🇳', 'ta' => '🇮🇳', 'hinglish' => '🔤'];
                    @endphp
                    <div class="glass rounded-2xl overflow-hidden">
                        <div class="p-4 bg-gradient-to-r from-{{ $color['from'] }} to-{{ $color['to'] }}">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">{{ $flags[$code] ?? '🌐' }}</span>
                                <div>
                                    <h3 class="font-bold text-white">{{ $langName }}</h3>
                                    <span class="text-white/80 text-sm uppercase">{{ $code }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="p-5 space-y-4">
                            <input type="hidden" name="templates[{{ $loop->index }}][language]" value="{{ $code }}">

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Question Text <span
                                        class="text-red-400">*</span></label>
                                <textarea name="templates[{{ $loop->index }}][question_text]" rows="4" required
                                    class="w-full bg-dark-300 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:border-{{ $color['focus'] }} focus:ring-1 focus:ring-{{ $color['focus'] }} transition-colors resize-none"
                                    placeholder="Enter question in {{ $langName }}...">{{ $template->question_text ?? '' }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Confirmation Text</label>
                                <input type="text" name="templates[{{ $loop->index }}][confirmation_text]"
                                    value="{{ $template->confirmation_text ?? '' }}"
                                    class="w-full bg-dark-300 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:border-{{ $color['focus'] }} focus:ring-1 focus:ring-{{ $color['focus'] }} transition-colors"
                                    placeholder="Thank you! You selected {value}">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Options Text</label>
                                <input type="text" name="templates[{{ $loop->index }}][options_text]"
                                    value="{{ $template->options_text ?? '' }}"
                                    class="w-full bg-dark-300 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:border-{{ $color['focus'] }} focus:ring-1 focus:ring-{{ $color['focus'] }} transition-colors"
                                    placeholder="Options: {options}">
                                <p class="text-gray-500 text-xs mt-1">Use {options} for list</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Error Text</label>
                                <input type="text" name="templates[{{ $loop->index }}][error_text]"
                                    value="{{ $template->error_text ?? '' }}"
                                    class="w-full bg-dark-300 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:border-{{ $color['focus'] }} focus:ring-1 focus:ring-{{ $color['focus'] }} transition-colors"
                                    placeholder="Invalid input, please try again">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Actions -->
            <div class="flex justify-center gap-4">
                <button type="submit"
                    class="bg-gradient-to-r from-green-500 to-emerald-500 px-8 py-3 rounded-xl text-white font-medium flex items-center gap-2 hover:shadow-lg hover:shadow-green-500/25 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save All Templates
                </button>
                <a href="{{ route('admin.questionnaire.templates.index') }}"
                    class="px-8 py-3 rounded-xl text-gray-400 hover:text-white font-medium bg-white/5 hover:bg-white/10 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection