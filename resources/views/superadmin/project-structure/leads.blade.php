@extends('superadmin.layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Header with Back Button -->
        <div class="flex items-center space-x-4">
            <a href="{{ route('superadmin.project-structure.show', ['module' => 'admins']) }}"
                class="p-2 rounded-xl bg-white/5 hover:bg-white/10 transition-colors">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">Leads - Product Quotation</h1>
                <p class="text-gray-400 mt-1">Project Structure > Admins > Leads</p>
            </div>
        </div>

        <!-- Lead Model Fields - Collapsible (Default Open) -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10 bg-gradient-to-r from-blue-500/10 to-indigo-500/10 cursor-pointer hover:bg-white/5 transition-colors"
                 onclick="toggleSection('lead-fields')">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center">👤</span>
                        Lead Model - Database Fields
                    </h2>
                    <svg id="lead-fields-icon" class="w-5 h-5 text-gray-400 transform transition-transform duration-300 rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
                <p class="text-sm text-gray-400 mt-1">Lead table ke main fields - kahan connected hain</p>
            </div>
            <div id="lead-fields" class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10 text-left">
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Field</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Type</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Description</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Status</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Connected To</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leadFields as $field)
                            <tr class="border-b border-white/5 hover:bg-white/5">
                                <td class="px-4 py-3 text-white font-medium">{{ $field['field'] }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 bg-blue-500/20 text-blue-400 rounded text-xs">{{ $field['type'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-300 text-sm">{{ $field['description'] }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-green-400">{{ $field['connection'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-green-400">{{ $field['connected_to'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Product Quotation Table Fields - Collapsible -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10 bg-gradient-to-r from-emerald-500/10 to-teal-500/10 cursor-pointer hover:bg-white/5 transition-colors"
                 onclick="toggleSection('product-quotation')">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center">📦</span>
                        Product Quotation (LeadProduct) - Table Fields
                    </h2>
                    <svg id="product-quotation-icon" class="w-5 h-5 text-gray-400 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
                <p class="text-sm text-gray-400 mt-1">Click to see: lead_products table ke fields</p>
            </div>
            <div id="product-quotation" class="hidden overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10 text-left">
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Field</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Type</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Description</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Status</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Connected To</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productQuotationFields as $field)
                            <tr class="border-b border-white/5 hover:bg-white/5">
                                <td class="px-4 py-3 text-white font-medium">{{ $field['field'] }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 rounded text-xs">{{ $field['type'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-300 text-sm">{{ $field['description'] }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-green-400">{{ $field['connection'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-green-400">{{ $field['connected_to'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Data Flow - Workflow to Product Quotation -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10 bg-gradient-to-r from-purple-500/10 to-pink-500/10 cursor-pointer hover:bg-white/5 transition-colors"
                 onclick="toggleSection('data-flow')">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">🔄</span>
                        Data Flow: WhatsApp → Product Quotation
                    </h2>
                    <svg id="data-flow-icon" class="w-5 h-5 text-gray-400 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
                <p class="text-sm text-gray-400 mt-1">Click to see: Step-by-step data flow</p>
            </div>
            <div id="data-flow" class="hidden overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10 text-left">
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Step</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Source</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-center">→</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Target</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Description</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">File</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dataFlow as $flow)
                            <tr class="border-b border-white/5 hover:bg-white/5">
                                <td class="px-4 py-3">
                                    <span class="w-8 h-8 rounded-full bg-purple-500/30 text-purple-400 flex items-center justify-center font-bold">{{ $flow['step'] }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 bg-blue-500/20 text-blue-400 rounded text-xs">{{ $flow['source'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-center text-purple-400 font-bold">→</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 bg-green-500/20 text-green-400 rounded text-xs">{{ $flow['target'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-300 text-sm">{{ $flow['description'] }}</td>
                                <td class="px-4 py-3">
                                    <code class="px-2 py-1 bg-white/10 rounded text-xs text-gray-300">{{ $flow['file'] }}</code>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Product Quotation Logic -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10 bg-gradient-to-r from-yellow-500/10 to-orange-500/10 cursor-pointer hover:bg-white/5 transition-colors"
                 onclick="toggleSection('quotation-logic')">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-yellow-500 to-orange-500 flex items-center justify-center">💡</span>
                        Product Quotation Logic - Kya Kaha Se Aata Hai?
                    </h2>
                    <svg id="quotation-logic-icon" class="w-5 h-5 text-gray-400 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
                <p class="text-sm text-gray-400 mt-1">Click to see: Table columns aur data kahan se aate hain</p>
            </div>
            <div id="quotation-logic" class="hidden overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10 text-left">
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Question</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Logic / Answer</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Status</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Reference File</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quotationLogic as $logic)
                            <tr class="border-b border-white/5 hover:bg-white/5">
                                <td class="px-4 py-3 text-white font-medium text-sm">{{ $logic['scenario'] }}</td>
                                <td class="px-4 py-3 text-gray-300 text-sm">{{ $logic['logic'] }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-green-400">{{ $logic['connected'] }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <code class="px-2 py-1 bg-white/10 rounded text-xs text-gray-300">{{ $logic['file'] }}</code>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Error Scenarios -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10 bg-gradient-to-r from-red-500/10 to-orange-500/10 cursor-pointer hover:bg-white/5 transition-colors"
                 onclick="toggleSection('error-section')">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-red-500 to-orange-500 flex items-center justify-center">⚠️</span>
                        Error / Disconnect Scenarios
                    </h2>
                    <svg id="error-section-icon" class="w-5 h-5 text-gray-400 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
                <p class="text-sm text-gray-400 mt-1">Click to see: Possible errors aur fixes</p>
            </div>
            <div id="error-section" class="hidden overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10 text-left">
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Scenario</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Type</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Cause</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Fix</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($errorScenarios as $error)
                            <tr class="border-b border-white/5 hover:bg-white/5">
                                <td class="px-4 py-3 text-white font-medium text-sm">{{ $error['scenario'] }}</td>
                                <td class="px-4 py-3">
                                    @if(str_contains($error['error_type'], '❌'))
                                        <span class="px-2 py-1 bg-red-500/20 text-red-400 rounded text-xs">{{ $error['error_type'] }}</span>
                                    @else
                                        <span class="px-2 py-1 bg-yellow-500/20 text-yellow-400 rounded text-xs">{{ $error['error_type'] }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-300 text-sm">{{ $error['cause'] }}</td>
                                <td class="px-4 py-3 text-green-400 text-sm">{{ $error['fix'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- References -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10 bg-gradient-to-r from-cyan-500/10 to-blue-500/10 cursor-pointer hover:bg-white/5 transition-colors"
                 onclick="toggleSection('ref-section')">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-500 to-blue-500 flex items-center justify-center">📁</span>
                        References - Code Files
                    </h2>
                    <svg id="ref-section-icon" class="w-5 h-5 text-gray-400 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
                <p class="text-sm text-gray-400 mt-1">Click to see: Connection logic kahan handle hota hai</p>
            </div>
            <div id="ref-section" class="hidden overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10 text-left">
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Service</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">File</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Connected To</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Logic</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($references as $ref)
                            <tr class="border-b border-white/5 hover:bg-white/5">
                                <td class="px-4 py-3 text-white font-medium text-sm">{{ $ref['service'] }}</td>
                                <td class="px-4 py-3">
                                    <code class="px-2 py-1 bg-white/10 rounded text-xs text-gray-300">{{ $ref['file'] }}</code>
                                </td>
                                <td class="px-4 py-3 text-green-400 text-sm">{{ $ref['connected_to'] }}</td>
                                <td class="px-4 py-3 text-gray-300 text-sm">{{ $ref['logic'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function toggleSection(sectionId) {
            const section = document.getElementById(sectionId);
            const icon = document.getElementById(sectionId + '-icon');
            
            if (section.classList.contains('hidden')) {
                section.classList.remove('hidden');
                icon.classList.add('rotate-90');
            } else {
                section.classList.add('hidden');
                icon.classList.remove('rotate-90');
            }
        }
    </script>
@endsection
