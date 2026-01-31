@extends('superadmin.layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Header with Back Button -->
        <div class="flex items-center space-x-4">
            <a href="{{ route('superadmin.project-structure.sub', ['module' => 'admins', 'submodule' => 'workflow']) }}"
                class="p-2 rounded-xl bg-white/5 hover:bg-white/10 transition-colors">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">Global Questions</h1>
                <p class="text-gray-400 mt-1">Project Structure > Admins > Workflow > Global Questions</p>
            </div>
        </div>

        <!-- Demo Fields Table -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10">
                <h2 class="text-lg font-semibold text-white">Demo Global Questions</h2>
                <p class="text-sm text-gray-400">Ye questions product questions se alag hain - customer info collect karte hain</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10 text-left">
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Question Name</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Field Name</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Type</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">⏱️ Trigger Position</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">🔗 After Field</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($demoFields as $field)
                            <tr class="border-b border-white/5 hover:bg-white/5">
                                <td class="px-4 py-3 text-white font-medium">{{ $field['name'] }}</td>
                                <td class="px-4 py-3">
                                    <code class="px-2 py-1 bg-white/10 rounded text-xs text-gray-300">{{ $field['field_name'] }}</code>
                                </td>
                                <td class="px-4 py-3">
                                    @if($field['question_type'] === 'text')
                                        <span class="px-2 py-1 bg-blue-500/20 text-blue-400 rounded text-xs">✏️ Text Input</span>
                                    @else
                                        <span class="px-2 py-1 bg-purple-500/20 text-purple-400 rounded text-xs">📋 Select</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($field['trigger_position'] === 'first')
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg bg-gradient-to-r from-green-500/20 to-emerald-500/20 text-green-400 text-xs font-medium">
                                            🥇 First (Sabse Pehle)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg bg-gradient-to-r from-orange-500/20 to-amber-500/20 text-orange-400 text-xs font-medium">
                                            ⏭️ After Field
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($field['trigger_after_field'])
                                        <code class="px-2 py-1 bg-orange-500/20 text-orange-400 rounded text-xs">{{ $field['trigger_after_field'] }}</code>
                                    @else
                                        <span class="text-gray-500">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Question Text Preview -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10 bg-gradient-to-r from-blue-500/10 to-cyan-500/10">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center">💬</span>
                    Question Text Preview
                </h2>
                <p class="text-sm text-gray-400 mt-1">Bot yeh questions user se puchega</p>
            </div>
            <div class="p-4 space-y-3">
                @foreach($demoFields as $field)
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-white/5">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500/20 to-pink-500/20 flex items-center justify-center text-sm flex-shrink-0">
                            {{ $loop->iteration }}
                        </div>
                        <div>
                            <p class="text-white font-medium">{{ $field['name'] }}</p>
                            <p class="text-gray-400 text-sm italic">"{{ $field['add_question'] }}"</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Trigger Position References -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10 bg-gradient-to-r from-green-500/10 to-emerald-500/10">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center">⏱️</span>
                    trigger_position - Kaha Use Hota Hai
                </h2>
                <p class="text-sm text-gray-400 mt-1">Question kab puche - first (pehle) ya after_field (baad mein)</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10 text-left">
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Service</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">File</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">UI Reference</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Logic</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($references['trigger_position'] as $ref)
                            <tr class="border-b border-white/5 hover:bg-white/5">
                                <td class="px-4 py-3 text-white font-medium">{{ $ref['service'] }}</td>
                                <td class="px-4 py-3">
                                    <code class="px-2 py-1 bg-white/10 rounded text-xs text-gray-300">{{ $ref['file'] }}</code>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 bg-purple-500/20 text-purple-400 rounded text-xs">{{ $ref['ui_reference'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-300 text-sm">{{ $ref['logic'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Trigger After Field References -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10 bg-gradient-to-r from-orange-500/10 to-amber-500/10">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-orange-500 to-amber-500 flex items-center justify-center">🔗</span>
                    trigger_after_field - Kaha Use Hota Hai
                </h2>
                <p class="text-sm text-gray-400 mt-1">Konse ProductQuestion ke baad yeh question puche</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10 text-left">
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Service</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">File</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">UI Reference</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Logic</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($references['trigger_after_field'] as $ref)
                            <tr class="border-b border-white/5 hover:bg-white/5">
                                <td class="px-4 py-3 text-white font-medium">{{ $ref['service'] }}</td>
                                <td class="px-4 py-3">
                                    <code class="px-2 py-1 bg-white/10 rounded text-xs text-gray-300">{{ $ref['file'] }}</code>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 bg-purple-500/20 text-purple-400 rounded text-xs">{{ $ref['ui_reference'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-300 text-sm">{{ $ref['logic'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Question Type References -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10 bg-gradient-to-r from-purple-500/10 to-pink-500/10">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">📝</span>
                    question_type - Kaha Use Hota Hai
                </h2>
                <p class="text-sm text-gray-400 mt-1">text = user type kare, select = options mein se choose</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10 text-left">
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Service</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">File</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">UI Reference</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Logic</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($references['question_type'] as $ref)
                            <tr class="border-b border-white/5 hover:bg-white/5">
                                <td class="px-4 py-3 text-white font-medium">{{ $ref['service'] }}</td>
                                <td class="px-4 py-3">
                                    <code class="px-2 py-1 bg-white/10 rounded text-xs text-gray-300">{{ $ref['file'] }}</code>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 bg-purple-500/20 text-purple-400 rounded text-xs">{{ $ref['ui_reference'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-300 text-sm">{{ $ref['logic'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
