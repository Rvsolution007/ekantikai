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
                <h1 class="text-2xl font-bold text-white">Product Questions</h1>
                <p class="text-gray-400 mt-1">Project Structure > Admins > Workflow > Product Questions</p>
            </div>
        </div>

        <!-- Demo Fields Table -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10">
                <h2 class="text-lg font-semibold text-white">Demo Fields</h2>
                <p class="text-sm text-gray-400">Ye fields live project mein bhi same hain</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10 text-left">
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Display Name</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Field Name</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Type</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-center">🔑 Unique Key</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-center">📦 Qty Field</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-center">🏷️ Unique Field
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($demoFields as $field)
                            <tr class="border-b border-white/5 hover:bg-white/5">
                                <td class="px-4 py-3 text-white font-medium">{{ $field['name'] }}</td>
                                <td class="px-4 py-3 text-gray-400">
                                    <code class="px-2 py-1 bg-white/10 rounded text-xs">{{ $field['field_name'] }}</code>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="px-2 py-1 bg-blue-500/20 text-blue-400 rounded text-xs">{{ $field['type'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($field['is_unique_key'])
                                        <span
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-orange-500 to-amber-500 text-white font-bold text-sm">
                                            {{ $field['unique_key_order'] }}
                                        </span>
                                    @else
                                        <span class="text-gray-500">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($field['is_qty_field'])
                                        <span
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-pink-500 text-white">
                                            ✓
                                        </span>
                                    @else
                                        <span class="text-gray-500">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($field['is_unique_field'])
                                        <span
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-green-500 to-emerald-500 text-white">
                                            ✓
                                        </span>
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

        <!-- Unique Key References -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10 bg-gradient-to-r from-orange-500/10 to-amber-500/10">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <span
                        class="w-8 h-8 rounded-lg bg-gradient-to-br from-orange-500 to-amber-500 flex items-center justify-center">🔑</span>
                    is_unique_key - Kaha Use Hota Hai
                </h2>
                <p class="text-sm text-gray-400 mt-1">Product ko unique identify karne ke liye (category|model|size|finish)
                </p>
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
                        @foreach($references['is_unique_key'] as $ref)
                            <tr class="border-b border-white/5 hover:bg-white/5">
                                <td class="px-4 py-3 text-white font-medium">{{ $ref['service'] }}</td>
                                <td class="px-4 py-3">
                                    <code class="px-2 py-1 bg-white/10 rounded text-xs text-gray-300">{{ $ref['file'] }}</code>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="px-2 py-1 bg-purple-500/20 text-purple-400 rounded text-xs">{{ $ref['ui_reference'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-300 text-sm">{{ $ref['logic'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Qty Field References -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10 bg-gradient-to-r from-purple-500/10 to-pink-500/10">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <span
                        class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">📦</span>
                    is_qty_field - Kaha Use Hota Hai
                </h2>
                <p class="text-sm text-gray-400 mt-1">User se quantity input lene ke liye</p>
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
                        @foreach($references['is_qty_field'] as $ref)
                            <tr class="border-b border-white/5 hover:bg-white/5">
                                <td class="px-4 py-3 text-white font-medium">{{ $ref['service'] }}</td>
                                <td class="px-4 py-3">
                                    <code class="px-2 py-1 bg-white/10 rounded text-xs text-gray-300">{{ $ref['file'] }}</code>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="px-2 py-1 bg-purple-500/20 text-purple-400 rounded text-xs">{{ $ref['ui_reference'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-300 text-sm">{{ $ref['logic'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Unique Field References -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10 bg-gradient-to-r from-green-500/10 to-emerald-500/10">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <span
                        class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center">🏷️</span>
                    is_unique_field - Kaha Use Hota Hai
                </h2>
                <p class="text-sm text-gray-400 mt-1">Model number se exact product identify karne ke liye</p>
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
                        @foreach($references['is_unique_field'] as $ref)
                            <tr class="border-b border-white/5 hover:bg-white/5">
                                <td class="px-4 py-3 text-white font-medium">{{ $ref['service'] }}</td>
                                <td class="px-4 py-3">
                                    <code class="px-2 py-1 bg-white/10 rounded text-xs text-gray-300">{{ $ref['file'] }}</code>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="px-2 py-1 bg-purple-500/20 text-purple-400 rounded text-xs">{{ $ref['ui_reference'] }}</span>
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