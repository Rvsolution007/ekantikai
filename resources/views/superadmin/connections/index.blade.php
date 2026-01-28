@extends('superadmin.layouts.app')

@section('title', 'System Connections')
@section('page-title', 'System Connections & Architecture')

@section('content')
    <div class="space-y-6">

        <!-- 🔍 CHATBOT DATA FLOW LOGIC (NEW SECTION) -->
        <div class="glass rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-white mb-2">🔍 Chatbot Data Flow Logic</h3>
            <p class="text-gray-400 text-sm mb-6">Step-by-step: Chatbot kaise data fetch karta hai aur validate karta hai
            </p>

            <!-- Step 1: Category Selection -->
            <div class="glass-light rounded-xl p-4 mb-4" x-data="{ open: true }">
                <div class="flex items-center justify-between cursor-pointer" @click="open = !open">
                    <div class="flex items-center gap-3">
                        <span
                            class="w-8 h-8 rounded-lg bg-green-500/20 text-green-400 flex items-center justify-center font-bold">1</span>
                        <h5 class="text-white font-semibold">User asks for "Hardware Items" → Category Options</h5>
                    </div>
                    <span class="text-gray-400" x-text="open ? '−' : '+'"></span>
                </div>

                <div x-show="open" x-collapse class="mt-4 space-y-3">
                    <div class="bg-gray-900/50 rounded-lg p-3 text-sm">
                        <p class="text-cyan-400 font-mono mb-2">// ProductQuestion me check karta hai</p>
                        <p class="text-gray-300"><span class="text-yellow-400">options_source</span> = <span
                                class="text-green-400">'catalogue'</span></p>
                        <p class="text-gray-300"><span class="text-yellow-400">catalogue_field</span> = <span
                                class="text-green-400">'category'</span></p>
                    </div>
                    <div class="bg-gray-900/50 rounded-lg p-3 text-sm">
                        <p class="text-cyan-400 font-mono mb-2">// SQL Query (Catalogue se unique categories):</p>
                        <code
                            class="text-green-400">SELECT DISTINCT JSON_EXTRACT(data, '$.category') FROM catalogues WHERE admin_id = ? AND is_active = 1</code>
                    </div>
                    <div class="bg-blue-500/10 rounded-lg p-3 text-sm border border-blue-500/30">
                        <p class="text-blue-400 font-medium">📤 Output:</p>
                        <p class="text-gray-300">Wardrobe handles, Wardrobe profile handle, Knob handles, Main door handles,
                            Cabinet handles, Profile handles</p>
                    </div>
                </div>
            </div>

            <!-- Step 2: Model Selection -->
            <div class="glass-light rounded-xl p-4 mb-4" x-data="{ open: true }">
                <div class="flex items-center justify-between cursor-pointer" @click="open = !open">
                    <div class="flex items-center gap-3">
                        <span
                            class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold">2</span>
                        <h5 class="text-white font-semibold">User selects "Profile handles" → Model Options</h5>
                    </div>
                    <span class="text-gray-400" x-text="open ? '−' : '+'"></span>
                </div>

                <div x-show="open" x-collapse class="mt-4 space-y-3">
                    <div class="bg-gray-900/50 rounded-lg p-3 text-sm">
                        <p class="text-cyan-400 font-mono mb-2">// ProductQuestion me check karta hai</p>
                        <p class="text-gray-300"><span class="text-yellow-400">options_source</span> = <span
                                class="text-green-400">'catalogue'</span></p>
                        <p class="text-gray-300"><span class="text-yellow-400">catalogue_field</span> = <span
                                class="text-green-400">'model_number'</span></p>
                        <p class="text-gray-300"><span class="text-yellow-400">depends_on</span> = <span
                                class="text-green-400">'product_category'</span> (previous answer)</p>
                    </div>
                    <div class="bg-gray-900/50 rounded-lg p-3 text-sm">
                        <p class="text-cyan-400 font-mono mb-2">// SQL Query (Models for selected category):</p>
                        <code
                            class="text-green-400">SELECT DISTINCT JSON_EXTRACT(data, '$.model_number') FROM catalogues WHERE admin_id = ? AND is_active = 1 AND JSON_EXTRACT(data, '$.category') = 'Profile handles'</code>
                    </div>
                    <div class="bg-red-500/10 rounded-lg p-3 text-sm border border-red-500/30">
                        <p class="text-red-400 font-medium">⚠️ Problem yahan ho sakti hai:</p>
                        <p class="text-gray-300">Agar AI prompt me <code class="text-cyan-400">previous_answer</code>
                            properly pass nahi ho raha, to wo saare models dikha dega instead of filtered ones</p>
                    </div>
                    <div class="bg-yellow-500/10 rounded-lg p-3 text-sm border border-yellow-500/30">
                        <p class="text-yellow-400 font-medium">🔧 Check karo:</p>
                        <p class="text-gray-300">AIService.php → <code class="text-cyan-400">buildPrompt()</code> me <code
                                class="text-cyan-400">collected_data</code> properly pass ho raha hai ya nahi</p>
                    </div>
                </div>
            </div>

            <!-- Step 3: Validation -->
            <div class="glass-light rounded-xl p-4 mb-4" x-data="{ open: true }">
                <div class="flex items-center justify-between cursor-pointer" @click="open = !open">
                    <div class="flex items-center gap-3">
                        <span
                            class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold">3</span>
                        <h5 class="text-white font-semibold">User says "9038" → Validation Step</h5>
                    </div>
                    <span class="text-gray-400" x-text="open ? '−' : '+'"></span>
                </div>

                <div x-show="open" x-collapse class="mt-4 space-y-3">
                    <div class="bg-gray-900/50 rounded-lg p-3 text-sm">
                        <p class="text-cyan-400 font-mono mb-2">// AI validates against catalogue:</p>
                        <code
                            class="text-green-400">SELECT * FROM catalogues WHERE admin_id = ? AND JSON_EXTRACT(data, '$.category') = 'Profile handles' AND JSON_EXTRACT(data, '$.model_number') = '9038'</code>
                    </div>
                    <div class="bg-red-500/10 rounded-lg p-3 text-sm border border-red-500/30">
                        <p class="text-red-400 font-medium">❌ Problem jo aapko mila:</p>
                        <p class="text-gray-300 mb-2">Step 2 me bot ne <span class="text-yellow-400">9038, 9039,
                                9040...</span> dikhaye (ye shayad kisi aur category ke models the)</p>
                        <p class="text-gray-300">Step 3 me validate kiya to <span class="text-red-400">"Profile handles" me
                                9038 nahi mila</span></p>
                        <p class="text-gray-300">Actual "Profile handles" ke models: <span class="text-green-400">16, 28,
                                29, 31, 32, 33, 34, 034 BS...</span></p>
                    </div>
                    <div class="bg-orange-500/10 rounded-lg p-3 text-sm border border-orange-500/30">
                        <p class="text-orange-400 font-medium">🐛 Root Cause:</p>
                        <p class="text-gray-300">AI ko model options dete waqt <code
                                class="text-cyan-400">collected_data.product_category</code> filter nahi laga, isliye sab
                            models aa gaye</p>
                    </div>
                </div>
            </div>

            <!-- Data Flow Diagram -->
            <div class="glass-light rounded-xl p-4 mb-4">
                <h5 class="text-white font-semibold mb-4">📊 Complete Data Flow Diagram</h5>
                <div class="overflow-x-auto">
                    <div class="min-w-[800px]">
                        <!-- Flow -->
                        <div class="flex items-center justify-between gap-2 text-xs">
                            <div class="bg-green-500/20 border border-green-500/30 rounded-lg p-3 text-center w-32">
                                <div class="text-green-400 font-bold">📱 WhatsApp</div>
                                <div class="text-gray-400">User Message</div>
                            </div>
                            <span class="text-gray-500">→</span>
                            <div class="bg-blue-500/20 border border-blue-500/30 rounded-lg p-3 text-center w-32">
                                <div class="text-blue-400 font-bold">🔌 Webhook</div>
                                <div class="text-gray-400">Receive</div>
                            </div>
                            <span class="text-gray-500">→</span>
                            <div class="bg-purple-500/20 border border-purple-500/30 rounded-lg p-3 text-center w-32">
                                <div class="text-purple-400 font-bold">❓ ProductQuestion</div>
                                <div class="text-gray-400">Get Options</div>
                            </div>
                            <span class="text-gray-500">→</span>
                            <div class="bg-cyan-500/20 border border-cyan-500/30 rounded-lg p-3 text-center w-32">
                                <div class="text-cyan-400 font-bold">📦 Catalogue</div>
                                <div class="text-gray-400">Filter by collected_data</div>
                            </div>
                            <span class="text-gray-500">→</span>
                            <div class="bg-yellow-500/20 border border-yellow-500/30 rounded-lg p-3 text-center w-32">
                                <div class="text-yellow-400 font-bold">🤖 AI Service</div>
                                <div class="text-gray-400">Generate Response</div>
                            </div>
                            <span class="text-gray-500">→</span>
                            <div class="bg-green-500/20 border border-green-500/30 rounded-lg p-3 text-center w-32">
                                <div class="text-green-400 font-bold">📱 WhatsApp</div>
                                <div class="text-gray-400">Send Reply</div>
                            </div>
                        </div>

                        <!-- Key Connection Points -->
                        <div class="mt-6 grid grid-cols-3 gap-4">
                            <div class="bg-gray-800/50 rounded-lg p-3">
                                <p class="text-cyan-400 font-medium text-sm">🔗 Key Connection 1</p>
                                <p class="text-gray-400 text-xs">ProductQuestion.<span
                                        class="text-yellow-400">catalogue_field</span> → Catalogue.<span
                                        class="text-green-400">data->{field}</span></p>
                            </div>
                            <div class="bg-gray-800/50 rounded-lg p-3">
                                <p class="text-cyan-400 font-medium text-sm">🔗 Key Connection 2</p>
                                <p class="text-gray-400 text-xs">WhatsappUser.<span
                                        class="text-yellow-400">collected_data</span> → Filter for next question</p>
                            </div>
                            <div class="bg-gray-800/50 rounded-lg p-3">
                                <p class="text-cyan-400 font-medium text-sm">🔗 Key Connection 3</p>
                                <p class="text-gray-400 text-xs">AI Prompt → <span
                                        class="text-yellow-400">previous_answers</span> + <span
                                        class="text-green-400">available_options</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Debug Checklist -->
            <div class="glass-light rounded-xl p-4">
                <h5 class="text-white font-semibold mb-3">🔧 Quick Debug Checklist</h5>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div class="flex items-start gap-2">
                        <span class="text-yellow-400">1.</span>
                        <span class="text-gray-300"><code class="text-cyan-400">product_questions.options_source</code> =
                            'catalogue' hai?</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-yellow-400">2.</span>
                        <span class="text-gray-300"><code class="text-cyan-400">product_questions.catalogue_field</code>
                            sahi column name hai?</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-yellow-400">3.</span>
                        <span class="text-gray-300"><code class="text-cyan-400">catalogues.data</code> me wo field exist
                            karta hai?</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-yellow-400">4.</span>
                        <span class="text-gray-300"><code class="text-cyan-400">whatsapp_users.collected_data</code> me
                            previous answers save ho rahe hain?</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-yellow-400">5.</span>
                        <span class="text-gray-300">AI prompt me <code class="text-cyan-400">collected_data</code> pass ho
                            raha hai?</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-yellow-400">6.</span>
                        <span class="text-gray-300">Options fetch karte waqt <code
                                class="text-cyan-400">previous category</code> se filter ho raha hai?</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Module Structure View -->
        <div class="glass rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-white mb-2">📊 Complete System Structure</h3>
            <p class="text-gray-400 text-sm mb-6">Har module ka Frontend → Route → Controller → Model → Database mapping
                with field-level details</p>

            @foreach($moduleStructure as $moduleKey => $module)
                <div class="mb-6">
                    <!-- Module Header -->
                    <div class="flex items-center gap-3 mb-4 pb-2 border-b border-white/10">
                        <span class="text-3xl">{{ $module['icon'] }}</span>
                        <h4 class="text-xl font-bold text-white">{{ $module['name'] }}</h4>
                    </div>

                    <!-- Submodules -->
                    @foreach($module['submodules'] as $subKey => $sub)
                        <div class="glass-light rounded-xl p-4 mb-4" x-data="{ open: false }">
                            <!-- Submodule Header -->
                            <div class="flex items-center justify-between cursor-pointer" @click="open = !open">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="px-2 py-1 text-xs rounded-lg {{ $sub['status'] === 'Active' ? 'bg-green-500/20 text-green-400' : ($sub['status'] === 'Empty' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400') }}">
                                        {{ $sub['status'] }}
                                    </span>
                                    <h5 class="text-white font-semibold">{{ $sub['name'] }}</h5>
                                </div>
                                <span class="text-gray-400 text-xl" x-text="open ? '−' : '+'"></span>
                            </div>
                            <p class="text-gray-400 text-sm mt-1">{{ $sub['description'] }}</p>

                            <!-- Expanded Content -->
                            <div x-show="open" x-collapse class="mt-4 space-y-4">
                                <!-- Layer Info -->
                                <div class="grid grid-cols-1 md:grid-cols-5 gap-2 text-xs">
                                    <div class="bg-blue-500/10 rounded-lg p-2">
                                        <span class="text-blue-400 font-medium block">Frontend</span>
                                        <span class="text-gray-300">{{ $sub['frontend'] }}</span>
                                    </div>
                                    <div class="bg-purple-500/10 rounded-lg p-2">
                                        <span class="text-purple-400 font-medium block">Route</span>
                                        <span class="text-gray-300">{{ $sub['route'] }}</span>
                                    </div>
                                    <div class="bg-cyan-500/10 rounded-lg p-2">
                                        <span class="text-cyan-400 font-medium block">Controller</span>
                                        <span class="text-gray-300">{{ $sub['controller'] }}</span>
                                    </div>
                                    <div class="bg-yellow-500/10 rounded-lg p-2">
                                        <span class="text-yellow-400 font-medium block">Model</span>
                                        <span class="text-gray-300">{{ $sub['model'] }}</span>
                                    </div>
                                    <div class="bg-green-500/10 rounded-lg p-2">
                                        <span class="text-green-400 font-medium block">Database</span>
                                        <span class="text-gray-300">{{ $sub['database'] }}</span>
                                    </div>
                                </div>

                                <!-- Fields Table -->
                                <div>
                                    <h6 class="text-white font-medium mb-2 flex items-center gap-2">
                                        📋 Database Fields
                                    </h6>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-xs">
                                            <thead>
                                                <tr class="border-b border-white/10">
                                                    <th class="text-left py-2 text-gray-400">UI Field</th>
                                                    <th class="text-left py-2 text-gray-400">DB Column</th>
                                                    <th class="text-left py-2 text-gray-400">Table</th>
                                                    <th class="text-left py-2 text-gray-400">Type</th>
                                                    <th class="text-left py-2 text-gray-400">Used In</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($sub['fields'] as $field)
                                                    <tr class="border-b border-white/5">
                                                        <td class="py-2 text-white">{{ $field['ui'] }}</td>
                                                        <td class="py-2 text-cyan-400 font-mono">{{ $field['db_column'] }}</td>
                                                        <td class="py-2 text-green-400 font-mono">{{ $field['db_table'] }}</td>
                                                        <td class="py-2">
                                                            <span
                                                                class="px-1.5 py-0.5 rounded bg-gray-700 text-gray-300">{{ $field['type'] }}</span>
                                                        </td>
                                                        <td class="py-2 text-gray-400">
                                                            @foreach($field['used_in'] as $usage)
                                                                <span
                                                                    class="inline-block px-1.5 py-0.5 rounded bg-purple-500/20 text-purple-400 mr-1 mb-1">{{ $usage }}</span>
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Connections -->
                                <div>
                                    <h6 class="text-white font-medium mb-2 flex items-center gap-2">
                                        🔗 Connections (Kahan use hota hai)
                                    </h6>
                                    <div class="space-y-2">
                                        @foreach($sub['connections'] as $conn)
                                            <div class="flex items-center gap-2 text-sm flex-wrap">
                                                <span class="text-gray-400">→</span>
                                                <span class="text-yellow-400 font-medium">{{ $conn['target'] }}</span>
                                                <span class="text-gray-500">via</span>
                                                <span class="text-cyan-400 font-mono text-xs">{{ $conn['via'] }}</span>
                                                <span class="text-gray-500">—</span>
                                                <span class="text-gray-300">{{ $conn['logic'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        <!-- 👤 PER-ADMIN BOT CONFIGURATION (NEW) -->
        <div class="glass rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-white mb-2">👤 Admin-wise Bot Configuration Status</h3>
            <p class="text-gray-400 text-sm mb-6">Har admin ka separate data - Flowchart, Product Questions, Catalogue
                connection</p>

            <div class="space-y-4">
                @foreach($adminConnections as $admin)
                    <div class="glass-light rounded-xl p-4" x-data="{ open: false }">
                        <!-- Admin Header -->
                        <div class="flex items-center justify-between cursor-pointer" @click="open = !open">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold">
                                    {{ substr($admin['admin_name'], 0, 1) }}
                                </div>
                                <div>
                                    <h5 class="text-white font-semibold">{{ $admin['admin_name'] }}</h5>
                                    <p class="text-gray-400 text-xs">Admin ID: {{ $admin['admin_id'] }}</p>
                                </div>
                                <!-- Status Badge -->
                                <span
                                    class="px-2 py-1 text-xs rounded-lg {{ $admin['status']['overall'] === 'fully_connected' ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400' }}">
                                    {{ $admin['status']['overall'] === 'fully_connected' ? '✅ Fully Connected' : '⚠️ Partial' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-4">
                                <!-- Summary Stats -->
                                <div class="flex gap-4 text-xs">
                                    <span class="text-gray-400">📝 {{ $admin['summary']['product_questions'] }} Fields</span>
                                    <span class="text-gray-400">🔀 {{ $admin['summary']['flowchart_nodes'] }} Nodes</span>
                                    <span class="text-gray-400">📦 {{ $admin['summary']['catalogue_items'] }} Products</span>
                                </div>
                                <span class="text-gray-400 text-xl" x-text="open ? '−' : '+'"></span>
                            </div>
                        </div>

                        <!-- Expanded Content -->
                        <div x-show="open" x-collapse class="mt-4 space-y-4">
                            <!-- Connection Status Grid -->
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
                                <div
                                    class="rounded-lg p-3 {{ $admin['status']['flowchart'] === 'connected' ? 'bg-green-500/10 border border-green-500/30' : 'bg-red-500/10 border border-red-500/30' }}">
                                    <span
                                        class="text-xs {{ $admin['status']['flowchart'] === 'connected' ? 'text-green-400' : 'text-red-400' }} font-medium block">🔀
                                        Flowchart</span>
                                    <span class="text-white font-bold">{{ $admin['summary']['flowchart_nodes'] }} nodes</span>
                                </div>
                                <div
                                    class="rounded-lg p-3 {{ $admin['status']['product_questions'] === 'connected' ? 'bg-green-500/10 border border-green-500/30' : 'bg-red-500/10 border border-red-500/30' }}">
                                    <span
                                        class="text-xs {{ $admin['status']['product_questions'] === 'connected' ? 'text-green-400' : 'text-red-400' }} font-medium block">📝
                                        Product Questions</span>
                                    <span class="text-white font-bold">{{ $admin['summary']['product_questions'] }}
                                        fields</span>
                                </div>
                                <div
                                    class="rounded-lg p-3 {{ $admin['status']['catalogue'] === 'connected' ? 'bg-green-500/10 border border-green-500/30' : 'bg-red-500/10 border border-red-500/30' }}">
                                    <span
                                        class="text-xs {{ $admin['status']['catalogue'] === 'connected' ? 'text-green-400' : 'text-red-400' }} font-medium block">📦
                                        Catalogue</span>
                                    <span class="text-white font-bold">{{ $admin['summary']['catalogue_items'] }} items</span>
                                </div>
                                <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-3">
                                    <span class="text-xs text-blue-400 font-medium block">📊 Lead Statuses</span>
                                    <span class="text-white font-bold">{{ $admin['summary']['lead_statuses'] }} statuses</span>
                                </div>
                                <div class="bg-purple-500/10 border border-purple-500/30 rounded-lg p-3">
                                    <span class="text-xs text-purple-400 font-medium block">🌍 Global Questions</span>
                                    <span class="text-white font-bold">{{ $admin['summary']['global_questions'] }}
                                        questions</span>
                                </div>
                            </div>

                            <!-- Field-Level Connections Table -->
                            @if(count($admin['field_connections']) > 0)
                                <div>
                                    <h6 class="text-white font-medium mb-2">📋 Field-Level Connection Status</h6>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-xs">
                                            <thead>
                                                <tr class="border-b border-white/10">
                                                    <th class="text-left py-2 text-gray-400">Field Name</th>
                                                    <th class="text-left py-2 text-gray-400">Display Name</th>
                                                    <th class="text-center py-2 text-gray-400">Catalogue</th>
                                                    <th class="text-center py-2 text-gray-400">Flowchart</th>
                                                    <th class="text-center py-2 text-gray-400">Unique Key</th>
                                                    <th class="text-center py-2 text-gray-400">Unique Field</th>
                                                    <th class="text-left py-2 text-gray-400">Options</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($admin['field_connections'] as $field)
                                                    <tr class="border-b border-white/5">
                                                        <td class="py-2 text-cyan-400 font-mono">{{ $field['field_name'] }}</td>
                                                        <td class="py-2 text-white">{{ $field['display_name'] }}</td>
                                                        <td class="py-2 text-center">
                                                            @if($field['catalogue_field']['connected'])
                                                                <span class="text-green-400">✅</span>
                                                            @else
                                                                <span class="text-red-400">❌</span>
                                                            @endif
                                                        </td>
                                                        <td class="py-2 text-center">
                                                            @if($field['flowchart_node']['connected'])
                                                                <span class="text-green-400">✅</span>
                                                            @else
                                                                <span class="text-red-400">❌</span>
                                                            @endif
                                                        </td>
                                                        <td class="py-2 text-center">
                                                            @if($field['is_unique_key'])
                                                                <span
                                                                    class="px-1.5 py-0.5 rounded bg-yellow-500/20 text-yellow-400">🔑</span>
                                                            @else
                                                                <span class="text-gray-500">-</span>
                                                            @endif
                                                        </td>
                                                        <td class="py-2 text-center">
                                                            @if($field['is_unique_field'])
                                                                <span
                                                                    class="px-1.5 py-0.5 rounded bg-purple-500/20 text-purple-400">🆔</span>
                                                            @else
                                                                <span class="text-gray-500">-</span>
                                                            @endif
                                                        </td>
                                                        <td class="py-2 text-gray-400">{{ $field['options_source'] ?? 'manual' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 🔗 HARDCODED FIELD CONNECTIONS (NEW) -->
        <div class="glass rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-white mb-2">🔗 System Data Flow Connections</h3>
            <p class="text-gray-400 text-sm mb-6">Frontend se Database tak every field ka connection path</p>

            <div class="space-y-4">
                @foreach($fieldConnections as $section)
                    <div class="glass-light rounded-xl p-4">
                        <h5 class="text-white font-semibold mb-1">{{ $section['section'] }}</h5>
                        <p class="text-gray-400 text-sm mb-4">{{ $section['description'] }}</p>

                        <div class="space-y-2">
                            @foreach($section['connections'] as $conn)
                                <div class="flex items-center gap-3 text-sm bg-gray-900/50 rounded-lg p-3">
                                    <span class="text-cyan-400 font-mono">{{ $conn['from'] }}</span>
                                    <span class="text-gray-400">→</span>
                                    <span class="text-green-400 font-mono">{{ $conn['to'] }}</span>
                                    <span class="px-2 py-0.5 rounded text-xs 
                                                    {{ $conn['status'] === 'auto_sync' ? 'bg-green-500/20 text-green-400' : '' }}
                                                    {{ $conn['status'] === 'foreign_key' ? 'bg-blue-500/20 text-blue-400' : '' }}
                                                    {{ $conn['status'] === 'dynamic_filter' ? 'bg-purple-500/20 text-purple-400' : '' }}
                                                    {{ $conn['status'] === 'field_mapping' ? 'bg-yellow-500/20 text-yellow-400' : '' }}
                                                    {{ $conn['status'] === 'data_copy' ? 'bg-cyan-500/20 text-cyan-400' : '' }}
                                                    {{ $conn['status'] === 'composite_key' ? 'bg-orange-500/20 text-orange-400' : '' }}
                                                    {{ $conn['status'] === 'status_update' ? 'bg-pink-500/20 text-pink-400' : '' }}
                                                ">{{ $conn['status'] }}</span>
                                    <span class="text-gray-500 text-xs ml-auto">{{ $conn['trigger'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection