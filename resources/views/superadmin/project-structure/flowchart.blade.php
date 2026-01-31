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
                <h1 class="text-2xl font-bold text-white">Flowchart Builder</h1>
                <p class="text-gray-400 mt-1">Project Structure > Admins > Workflow > Flowchart</p>
            </div>
        </div>

        <!-- Demo Node Types -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10 bg-gradient-to-r from-purple-500/10 to-pink-500/10">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <span
                        class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">🔷</span>
                    Node Types
                </h2>
                <p class="text-sm text-gray-400 mt-1">Flowchart mein 5 type ke nodes hote hain</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10 text-left">
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Type</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Name</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Description</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Example</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($demoNodes as $node)
                            <tr class="border-b border-white/5 hover:bg-white/5">
                                <td class="px-4 py-3">
                                    <span class="text-2xl">{{ $node['icon'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-white font-medium">{{ $node['name'] }}</td>
                                <td class="px-4 py-3 text-gray-300 text-sm">{{ $node['description'] }}</td>
                                <td class="px-4 py-3 text-gray-400 text-sm italic">{{ $node['example'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Demo Connections -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10 bg-gradient-to-r from-blue-500/10 to-cyan-500/10">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <span
                        class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center">🔗</span>
                    Connections (Edges)
                </h2>
                <p class="text-sm text-gray-400 mt-1">Nodes ek dusre se kaise connect hote hain</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10 text-left">
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Source Node</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-center">→</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Target Node</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Label</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($demoConnections as $conn)
                            <tr class="border-b border-white/5 hover:bg-white/5">
                                <td class="px-4 py-3">
                                    <span
                                        class="px-2 py-1 bg-green-500/20 text-green-400 rounded text-xs">{{ $conn['source'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-center text-purple-400 font-bold">→</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="px-2 py-1 bg-blue-500/20 text-blue-400 rounded text-xs">{{ $conn['target'] }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <code
                                        class="px-2 py-1 bg-white/10 rounded text-xs text-gray-300">{{ $conn['label'] }}</code>
                                </td>
                                <td class="px-4 py-3 text-gray-400 text-sm">{{ $conn['description'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Node Types References -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10 bg-gradient-to-r from-indigo-500/10 to-purple-500/10">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <span
                        class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center">📦</span>
                    Node Types - Kaha Use Hota Hai
                </h2>
                <p class="text-sm text-gray-400 mt-1">Node types ka code kahan define hai aur kahan use hota hai</p>
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
                        @foreach($references['node_types'] as $ref)
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

        <!-- Connections References -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10 bg-gradient-to-r from-cyan-500/10 to-blue-500/10">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <span
                        class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-500 to-blue-500 flex items-center justify-center">🔗</span>
                    Connections - Kaha Use Hota Hai
                </h2>
                <p class="text-sm text-gray-400 mt-1">Connections (edges) ka code kahan define hai aur kahan use hota hai
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
                        @foreach($references['connections'] as $ref)
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

        <!-- Sync Logic References -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10 bg-gradient-to-r from-amber-500/10 to-orange-500/10">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <span
                        class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center">🔄</span>
                    Sync Logic - ProductQuestion Se Sync
                </h2>
                <p class="text-sm text-gray-400 mt-1">Flowchart aur Product Questions kaise sync hote hain</p>
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
                        @foreach($references['sync_logic'] as $ref)
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

        <!-- Save Operations References -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10 bg-gradient-to-r from-green-500/10 to-emerald-500/10">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <span
                        class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center">💾</span>
                    Save Operations - Kaise Save Hota Hai
                </h2>
                <p class="text-sm text-gray-400 mt-1">Flowchart data kaise save, load aur clear hota hai</p>
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
                        @foreach($references['save_operations'] as $ref)
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

        <!-- Question Order Priority References - NEW -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10 bg-gradient-to-r from-yellow-500/10 to-amber-500/10">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <span
                        class="w-8 h-8 rounded-lg bg-gradient-to-br from-yellow-500 to-amber-500 flex items-center justify-center">📋</span>
                    Question Order Priority - Bot Kaise Order Follow Karta Hai
                </h2>
                <p class="text-sm text-gray-400 mt-1">Bot flowchart connections ke hisaab se questions puchta hai,
                    ProductQuestion.sort_order nahi dekhta</p>
            </div>
            <div class="p-4 bg-gradient-to-r from-yellow-500/5 to-amber-500/5">
                <div class="flex items-center gap-2 text-sm text-amber-400 mb-4">
                    <span class="text-lg">⚡</span>
                    <strong>IMPORTANT:</strong> Flowchart Builder mein jo order set karo, bot wahi order follow karega!
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="bg-white/5 p-3 rounded-lg">
                        <div class="text-green-400 font-medium mb-2">✅ Correct Flow:</div>
                        <code class="text-gray-300">Start → Category → Model → Size → Finish → End</code>
                    </div>
                    <div class="bg-white/5 p-3 rounded-lg">
                        <div class="text-blue-400 font-medium mb-2">📦 Data Source:</div>
                        <code class="text-gray-300">questionnaire_connections table (source → target)</code>
                    </div>
                </div>
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
                        @foreach($references['question_order_priority'] as $ref)
                            <tr class="border-b border-white/5 hover:bg-white/5">
                                <td class="px-4 py-3 text-white font-medium">{{ $ref['service'] }}</td>
                                <td class="px-4 py-3">
                                    <code class="px-2 py-1 bg-white/10 rounded text-xs text-gray-300">{{ $ref['file'] }}</code>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="px-2 py-1 bg-yellow-500/20 text-yellow-400 rounded text-xs">{{ $ref['ui_reference'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-300 text-sm">{{ $ref['logic'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>


        <!-- Node Properties Demo -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10 bg-gradient-to-r from-rose-500/10 to-pink-500/10">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <span
                        class="w-8 h-8 rounded-lg bg-gradient-to-br from-rose-500 to-pink-500 flex items-center justify-center">⚙️</span>
                    Node Properties (Right Sidebar)
                </h2>
                <p class="text-sm text-gray-400 mt-1">Jab koi node select karo to right side mein ye fields dikhte hain</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10 text-left">
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Field</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Type</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Description</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Connection</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Connected File</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($demoNodeProperties as $prop)
                            <tr class="border-b border-white/5 hover:bg-white/5">
                                <td class="px-4 py-3 text-white font-medium">{{ $prop['field'] }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="px-2 py-1 bg-blue-500/20 text-blue-400 rounded text-xs">{{ $prop['type'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-300 text-sm">{{ $prop['description'] }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if(str_contains($prop['connection'], '✅'))
                                        <span class="text-green-400">{{ $prop['connection'] }}</span>
                                    @else
                                        <span class="text-red-400">{{ $prop['connection'] }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <code
                                        class="px-2 py-1 bg-white/10 rounded text-xs text-gray-300">{{ $prop['connected_file'] }}</code>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Node Properties References -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10 bg-gradient-to-r from-fuchsia-500/10 to-rose-500/10">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <span
                        class="w-8 h-8 rounded-lg bg-gradient-to-br from-fuchsia-500 to-rose-500 flex items-center justify-center">🎛️</span>
                    Node Properties - Kaha Use Hota Hai
                </h2>
                <p class="text-sm text-gray-400 mt-1">Har field ka code kahan handle hota hai with connections</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10 text-left">
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Field</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Service</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">File</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Connected To</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase">Logic Flow</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($nodePropertiesReferences as $ref)
                            <tr class="border-b border-white/5 hover:bg-white/5">
                                <td class="px-4 py-3">
                                    <span
                                        class="px-2 py-1 bg-rose-500/20 text-rose-400 rounded text-xs font-medium">{{ $ref['field'] }}</span>
                                </td>
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
@endsection