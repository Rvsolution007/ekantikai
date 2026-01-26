@extends('superadmin.layouts.app')

@section('title', 'System Connections')
@section('page-title', 'System Connections & Architecture')

@section('content')
    <div class="space-y-6" x-data="systemConnections()">

        <!-- Quick Status Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
            @foreach ($connections as $key => $conn)
                <div class="glass rounded-xl p-4 cursor-pointer hover:bg-white/10 transition-colors"
                    @click="showDetails('{{ $key }}')">
                    <div class="text-2xl mb-2">{{ $conn['icon'] }}</div>
                    <div class="text-white font-medium text-sm">{{ $conn['name'] }}</div>
                    <span
                        class="text-xs px-2 py-0.5 rounded-lg {{ $conn['status'] === 'Active' ? 'bg-green-500/20 text-green-400' : ($conn['status'] === 'Empty' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400') }}">
                        {{ $conn['status'] }}
                    </span>
                </div>
            @endforeach
        </div>

        <!-- Tab Navigation -->
        <div class="glass rounded-2xl p-2 flex flex-wrap gap-2">
            <button @click="activeTab = 'architecture'"
                :class="activeTab === 'architecture' ? 'bg-primary-600 text-white' : 'text-gray-400 hover:text-white'"
                class="px-4 py-2 rounded-xl font-medium transition-colors">
                🏗️ Architecture
            </button>
            <button @click="activeTab = 'connections'"
                :class="activeTab === 'connections' ? 'bg-primary-600 text-white' : 'text-gray-400 hover:text-white'"
                class="px-4 py-2 rounded-xl font-medium transition-colors">
                🔗 Component Connections
            </button>
            <button @click="activeTab = 'database'"
                :class="activeTab === 'database' ? 'bg-primary-600 text-white' : 'text-gray-400 hover:text-white'"
                class="px-4 py-2 rounded-xl font-medium transition-colors">
                🗄️ Database Tables
            </button>
            <button @click="activeTab = 'hardcoded'"
                :class="activeTab === 'hardcoded' ? 'bg-primary-600 text-white' : 'text-gray-400 hover:text-white'"
                class="px-4 py-2 rounded-xl font-medium transition-colors">
                ⚠️ Hardcoded Items
            </button>
            <button @click="activeTab = 'flow'"
                :class="activeTab === 'flow' ? 'bg-primary-600 text-white' : 'text-gray-400 hover:text-white'"
                class="px-4 py-2 rounded-xl font-medium transition-colors">
                🔄 Data Flow
            </button>
        </div>

        <!-- Architecture Tab -->
        <div x-show="activeTab === 'architecture'" x-cloak class="space-y-6">
            <div class="glass rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4">🏗️ System Architecture Overview</h3>
                <div class="overflow-x-auto">
                    <div class="min-w-[900px]">
                        <!-- Main Flow -->
                        <div class="flex items-center justify-center gap-3 mb-8">
                            <div class="glass-light px-4 py-3 rounded-xl text-center">
                                <div class="text-2xl">📱</div>
                                <div class="text-green-400 font-mono text-sm">WhatsApp</div>
                            </div>
                            <span class="text-gray-500 text-2xl">→</span>
                            <div class="glass-light px-4 py-3 rounded-xl text-center">
                                <div class="text-2xl">🔌</div>
                                <div class="text-blue-400 font-mono text-sm">Webhook</div>
                            </div>
                            <span class="text-gray-500 text-2xl">→</span>
                            <div class="glass-light px-4 py-3 rounded-xl text-center">
                                <div class="text-2xl">🤖</div>
                                <div class="text-purple-400 font-mono text-sm">AIService</div>
                            </div>
                            <span class="text-gray-500 text-2xl">→</span>
                            <div class="glass-light px-4 py-3 rounded-xl text-center">
                                <div class="text-2xl">🧠</div>
                                <div class="text-yellow-400 font-mono text-sm">Gemini/OpenAI</div>
                            </div>
                            <span class="text-gray-500 text-2xl">→</span>
                            <div class="glass-light px-4 py-3 rounded-xl text-center">
                                <div class="text-2xl">💬</div>
                                <div class="text-green-400 font-mono text-sm">Response</div>
                            </div>
                        </div>

                        <!-- Component breakdown -->
                        <div class="grid grid-cols-3 gap-4">
                            <!-- Inputs to AI -->
                            <div class="glass-light rounded-xl p-4">
                                <h4 class="text-cyan-400 font-semibold mb-3 flex items-center gap-2">
                                    📥 AI Ko Milta Hai (Prompt Context)
                                </h4>
                                <ul class="space-y-2 text-sm">
                                    <li class="flex items-center gap-2 text-gray-300">
                                        <span class="text-green-400">✓</span> Admin System Prompt
                                    </li>
                                    <li class="flex items-center gap-2 text-gray-300">
                                        <span class="text-green-400">✓</span> Product Questions (workflow fields)
                                    </li>
                                    <li class="flex items-center gap-2 text-gray-300">
                                        <span class="text-green-400">✓</span> Global Questions
                                    </li>
                                    <li class="flex items-center gap-2 text-gray-300">
                                        <span class="text-green-400">✓</span> Catalogue Data (for validation)
                                    </li>
                                    <li class="flex items-center gap-2 text-gray-300">
                                        <span class="text-green-400">✓</span> Chat History
                                    </li>
                                    <li class="flex items-center gap-2 text-gray-300">
                                        <span class="text-green-400">✓</span> Current Workflow State
                                    </li>
                                </ul>
                            </div>

                            <!-- AI Processing -->
                            <div class="glass-light rounded-xl p-4">
                                <h4 class="text-purple-400 font-semibold mb-3 flex items-center gap-2">
                                    🧠 AI Kya Karta Hai
                                </h4>
                                <ul class="space-y-2 text-sm">
                                    <li class="flex items-center gap-2 text-gray-300">
                                        <span class="text-blue-400">→</span> Intent samajhta hai (product query?)
                                    </li>
                                    <li class="flex items-center gap-2 text-gray-300">
                                        <span class="text-blue-400">→</span> Workflow follow karta hai
                                    </li>
                                    <li class="flex items-center gap-2 text-gray-300">
                                        <span class="text-blue-400">→</span> Catalogue se validate karta hai
                                    </li>
                                    <li class="flex items-center gap-2 text-gray-300">
                                        <span class="text-blue-400">→</span> Next question decide karta hai
                                    </li>
                                    <li class="flex items-center gap-2 text-gray-300">
                                        <span class="text-blue-400">→</span> JSON response bhejta hai
                                    </li>
                                </ul>
                            </div>

                            <!-- Outputs -->
                            <div class="glass-light rounded-xl p-4">
                                <h4 class="text-green-400 font-semibold mb-3 flex items-center gap-2">
                                    📤 Output Actions
                                </h4>
                                <ul class="space-y-2 text-sm">
                                    <li class="flex items-center gap-2 text-gray-300">
                                        <span class="text-yellow-400">★</span> Lead create/update
                                    </li>
                                    <li class="flex items-center gap-2 text-gray-300">
                                        <span class="text-yellow-400">★</span> LeadProduct save (quotation)
                                    </li>
                                    <li class="flex items-center gap-2 text-gray-300">
                                        <span class="text-yellow-400">★</span> Chat message save
                                    </li>
                                    <li class="flex items-center gap-2 text-gray-300">
                                        <span class="text-yellow-400">★</span> Workflow state update
                                    </li>
                                    <li class="flex items-center gap-2 text-gray-300">
                                        <span class="text-yellow-400">★</span> WhatsApp response send
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Component Connections Tab -->
        <div x-show="activeTab === 'connections'" x-cloak class="space-y-6">
            <div class="glass rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-white/10">
                    <h3 class="text-lg font-semibold text-white">🔗 Component Connections</h3>
                    <p class="text-gray-400 text-sm mt-1">Kaunsa component kisse connected hai</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($connections as $key => $conn)
                            <div class="glass-light rounded-xl p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-2xl">{{ $conn['icon'] }}</span>
                                        <div>
                                            <h4 class="text-white font-medium">{{ $conn['name'] }}</h4>
                                            <p class="text-gray-500 text-xs">{{ $conn['description'] }}</p>
                                        </div>
                                    </div>
                                    <span
                                        class="px-2 py-1 text-xs rounded-lg {{ $conn['status'] === 'Active' ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400' }}">
                                        {{ $conn['status'] }}
                                    </span>
                                </div>

                                <div class="space-y-2 text-sm">
                                    <div>
                                        <span class="text-gray-500">Tables:</span>
                                        <span class="text-cyan-400">{{ implode(', ', $conn['tables']) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Controller:</span>
                                        <span class="text-blue-400">{{ implode(', ', $conn['controllers']) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Connects to:</span>
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            @foreach ($conn['connects_to'] as $target)
                                                <span
                                                    class="px-2 py-0.5 rounded-lg bg-purple-500/20 text-purple-400 text-xs">{{ $target }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- File Connections -->
            <div class="glass rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-white/10">
                    <h3 class="text-lg font-semibold text-white">📁 File-to-File Connections</h3>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-white/10">
                                    <th class="text-left py-3 text-gray-400">Source</th>
                                    <th class="text-center py-3 text-gray-400">→</th>
                                    <th class="text-left py-3 text-gray-400">Target</th>
                                    <th class="text-left py-3 text-gray-400">Method/Route</th>
                                    <th class="text-left py-3 text-gray-400">Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fileConnections as $fc)
                                    <tr class="border-b border-white/5">
                                        <td class="py-3 text-blue-400">{{ $fc['source'] }}</td>
                                        <td class="py-3 text-center text-gray-500">→</td>
                                        <td class="py-3 text-purple-400">{{ $fc['target'] }}</td>
                                        <td class="py-3 text-cyan-400 font-mono text-xs">
                                            {{ $fc['method'] ?? $fc['route'] ?? '-' }}
                                        </td>
                                        <td class="py-3 text-gray-400">{{ $fc['description'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Database Tab -->
        <div x-show="activeTab === 'database'" x-cloak>
            <div class="glass rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-white/10">
                    <h3 class="text-lg font-semibold text-white">🗄️ Database Tables</h3>
                    <p class="text-gray-400 text-sm mt-1">All tables with record counts</p>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-white/10">
                                    <th class="text-left py-3 text-gray-400">Table</th>
                                    <th class="text-left py-3 text-gray-400">Description</th>
                                    <th class="text-right py-3 text-gray-400">Records</th>
                                    <th class="text-center py-3 text-gray-400">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($databaseConnections as $db)
                                    <tr class="border-b border-white/5 hover:bg-white/5">
                                        <td class="py-3 text-white font-mono">{{ $db['table'] }}</td>
                                        <td class="py-3 text-gray-400">{{ $db['description'] }}</td>
                                        <td class="py-3 text-right">
                                            <span
                                                class="px-2 py-1 rounded-lg {{ $db['count'] > 0 ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400' }}">
                                                {{ number_format($db['count']) }}
                                            </span>
                                        </td>
                                        <td class="py-3 text-center">
                                            <span
                                                class="px-2 py-1 text-xs rounded-lg {{ $db['status'] === 'Active' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                                                {{ $db['status'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hardcoded Items Tab -->
        <div x-show="activeTab === 'hardcoded'" x-cloak>
            <div class="glass rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-white/10">
                    <h3 class="text-lg font-semibold text-white">⚠️ Hardcoded Items Found</h3>
                    <p class="text-gray-400 text-sm mt-1">Items that may need attention when making changes</p>
                </div>
                <div class="p-6">
                    @if (count($hardcodedItems) > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-white/10">
                                        <th class="text-left py-3 text-gray-400">Type</th>
                                        <th class="text-left py-3 text-gray-400">Value</th>
                                        <th class="text-left py-3 text-gray-400">File</th>
                                        <th class="text-left py-3 text-gray-400">Concern</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($hardcodedItems as $item)
                                        <tr class="border-b border-white/5">
                                            <td class="py-3">
                                                <span
                                                    class="px-2 py-1 rounded-lg {{ str_contains($item['concern'], '✅') ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400' }} text-xs">
                                                    {{ $item['type'] }}
                                                </span>
                                            </td>
                                            <td class="py-3 text-white font-mono text-xs">{{ $item['value'] }}</td>
                                            <td class="py-3 text-blue-400">{{ $item['file'] }}</td>
                                            <td class="py-3 text-gray-400">{{ $item['concern'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="text-4xl mb-4">✅</div>
                            <p class="text-green-400 font-medium">No critical hardcoded items found!</p>
                            <p class="text-gray-400 text-sm mt-1">System is using dynamic configuration</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Data Flow Tab -->
        <div x-show="activeTab === 'flow'" x-cloak>
            <div class="glass rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-white mb-6">🔄 Complete Data Flow</h3>

                <div class="space-y-6">
                    <!-- Step 1 -->
                    <div class="glass-light rounded-xl p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <span
                                class="w-8 h-8 rounded-lg bg-green-500/20 text-green-400 flex items-center justify-center font-bold">1</span>
                            <h4 class="text-white font-medium">WhatsApp Message Arrives</h4>
                        </div>
                        <p class="text-gray-400 text-sm ml-11">User message → <code
                                class="text-cyan-400">POST /api/webhook</code> → <code
                                class="text-blue-400">WebhookController@handle</code></p>
                    </div>

                    <!-- Step 2 -->
                    <div class="glass-light rounded-xl p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <span
                                class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold">2</span>
                            <h4 class="text-white font-medium">Context Building</h4>
                        </div>
                        <div class="ml-11 text-sm space-y-1">
                            <p class="text-gray-400">→ <code class="text-purple-400">ProductQuestion</code> fetch
                                (workflow fields)</p>
                            <p class="text-gray-400">→ <code class="text-purple-400">GlobalQuestion</code> fetch</p>
                            <p class="text-gray-400">→ <code class="text-purple-400">Catalogue</code> search for
                                validation</p>
                            <p class="text-gray-400">→ <code class="text-purple-400">ChatMessage</code> history</p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="glass-light rounded-xl p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <span
                                class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold">3</span>
                            <h4 class="text-white font-medium">AI Processing</h4>
                        </div>
                        <p class="text-gray-400 text-sm ml-11">
                            <code class="text-yellow-400">AIService@generateResponse</code> → Gemini/OpenAI API call →
                            JSON response
                        </p>
                    </div>

                    <!-- Step 4 -->
                    <div class="glass-light rounded-xl p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <span
                                class="w-8 h-8 rounded-lg bg-yellow-500/20 text-yellow-400 flex items-center justify-center font-bold">4</span>
                            <h4 class="text-white font-medium">Data Saving</h4>
                        </div>
                        <div class="ml-11 text-sm space-y-1">
                            <p class="text-gray-400">→ <code class="text-green-400">Lead</code> create/update</p>
                            <p class="text-gray-400">→ <code class="text-green-400">LeadProduct</code> save product
                                selections</p>
                            <p class="text-gray-400">→ <code class="text-green-400">ChatMessage</code> save both
                                messages</p>
                            <p class="text-gray-400">→ <code class="text-green-400">WhatsappUser</code> workflow state
                                update</p>
                        </div>
                    </div>

                    <!-- Step 5 -->
                    <div class="glass-light rounded-xl p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <span
                                class="w-8 h-8 rounded-lg bg-green-500/20 text-green-400 flex items-center justify-center font-bold">5</span>
                            <h4 class="text-white font-medium">Response Sent</h4>
                        </div>
                        <p class="text-gray-400 text-sm ml-11">WhatsApp API call → Message delivered to user</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Modal -->
        <div x-show="detailModal" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="detailModal = false">
            <div class="glass rounded-2xl p-6 max-w-lg w-full max-h-[80vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-white" x-text="selectedComponent?.name"></h3>
                    <button @click="detailModal = false" class="text-gray-400 hover:text-white">✕</button>
                </div>
                <div x-show="selectedComponent" class="space-y-3 text-sm">
                    <p class="text-gray-400" x-text="selectedComponent?.description"></p>
                    <div>
                        <span class="text-gray-500">Tables:</span>
                        <span class="text-cyan-400" x-text="selectedComponent?.tables?.join(', ')"></span>
                    </div>
                    <div>
                        <span class="text-gray-500">Model:</span>
                        <span class="text-purple-400" x-text="selectedComponent?.model"></span>
                    </div>
                    <div>
                        <span class="text-gray-500">Views:</span>
                        <span class="text-yellow-400" x-text="selectedComponent?.views"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function systemConnections() {
            return {
                activeTab: 'architecture',
                detailModal: false,
                selectedComponent: null,
                connections: @json($connections),

                showDetails(key) {
                    this.selectedComponent = this.connections[key];
                    this.detailModal = true;
                }
            }
        }
    </script>
@endsection