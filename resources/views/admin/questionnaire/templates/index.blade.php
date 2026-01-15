@extends('admin.layouts.app')

@section('title', 'Question Templates')

@section('content')
<div class="p-4 lg:p-6">
    <!-- Header -->
    <div class="glass rounded-2xl p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                        </svg>
                    </div>
                    Question Templates
                </h1>
                <p class="text-gray-400 mt-1">Multi-language question texts for each field</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="px-3 py-1.5 rounded-lg bg-orange-500/20 text-orange-400 text-sm font-medium">🇮🇳 Hindi</span>
                <span class="px-3 py-1.5 rounded-lg bg-blue-500/20 text-blue-400 text-sm font-medium">🇬🇧 English</span>
                <span class="px-3 py-1.5 rounded-lg bg-purple-500/20 text-purple-400 text-sm font-medium">🇮🇳 Gujarati</span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="glass-light rounded-xl p-4 mb-6 border-l-4 border-green-500 flex items-center gap-3">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="text-green-400">{{ session('success') }}</span>
        </div>
    @endif

    <!-- All Fields Templates -->
    <div class="glass rounded-2xl overflow-hidden mb-6">
        <div class="p-4 border-b border-white/10">
            <h2 class="font-semibold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Field Templates
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/10">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Field Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">Display Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Languages</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Preview</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider w-28">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($fields as $field)
                        @php
                            $fieldTemplates = $templatesByField[$field->field_name] ?? collect();
                            $configuredLangs = $fieldTemplates->pluck('language')->toArray();
                        @endphp
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-4 py-4">
                                <span class="px-3 py-1.5 rounded-lg bg-green-500/20 text-green-300 font-mono text-sm">{{ $field->field_name }}</span>
                            </td>
                            <td class="px-4 py-4 hidden md:table-cell">
                                <span class="text-white">{{ $field->display_name }}</span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach(['hi' => 'HI', 'en' => 'EN', 'gu' => 'GU'] as $code => $name)
                                        @if(in_array($code, $configuredLangs))
                                            <span class="px-2 py-1 rounded text-xs font-medium bg-green-500/20 text-green-400 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                {{ $name }}
                                            </span>
                                        @else
                                            <span class="px-2 py-1 rounded text-xs font-medium bg-red-500/20 text-red-400 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                                {{ $name }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-4 hidden lg:table-cell">
                                @if($fieldTemplates->where('language', 'hi')->first())
                                    <span class="text-gray-400 text-sm truncate block max-w-xs">{{ Str::limit($fieldTemplates->where('language', 'hi')->first()->question_text, 40) }}</span>
                                @else
                                    <span class="text-gray-500 text-sm">No template</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                <a href="{{ route('admin.questionnaire.templates.edit', $field->field_name) }}" 
                                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-500/20 hover:bg-green-500/30 text-green-400 text-sm font-medium transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400">No fields configured</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Back Link -->
    <div class="text-center">
        <a href="{{ route('admin.questionnaire.fields.index') }}" class="inline-flex items-center gap-2 text-gray-400 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Fields
        </a>
    </div>
</div>
@endsection