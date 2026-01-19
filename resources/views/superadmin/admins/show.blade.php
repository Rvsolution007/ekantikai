@extends('superadmin.layouts.app')

@section('title', 'View Admin')

@section('content')
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white mb-2">{{ $tenant->name }}</h1>
                <p class="text-gray-400">Admin Details & Statistics</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('superadmin.admins.edit', $tenant) }}"
                    class="btn-gradient px-4 py-2 rounded-lg inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Admin
                </a>
                <a href="{{ route('superadmin.admins.index') }}"
                    class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Admins
                </a>
            </div>
        </div>
    </div>

    <!-- Client Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="glass-card p-6 rounded-xl">
            <div class="flex items-center justify-between mb-4">
                <span class="text-gray-400 text-sm">Status</span>
                @if($tenant->is_active)
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400">Active</span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-red-500/20 text-red-400">Inactive</span>
                @endif
            </div>
            <p class="text-2xl font-bold text-white capitalize">{{ $tenant->is_active ? 'Active' : 'Inactive' }}</p>
        </div>

        <div class="glass-card p-6 rounded-xl">
            <div class="flex items-center justify-between mb-4">
                <span class="text-gray-400 text-sm">Subscription Plan</span>
                <div class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-white capitalize">{{ $tenant->subscription_plan ?? 'Free' }}</p>
        </div>

        <div class="glass-card p-6 rounded-xl">
            <div class="flex items-center justify-between mb-4">
                <span class="text-gray-400 text-sm">Available Credits</span>
                <div class="w-10 h-10 rounded-lg bg-green-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-white">{{ number_format($tenant->credits->available_credits ?? 0) }}</p>
        </div>

        <div class="glass-card p-6 rounded-xl">
            <div class="flex items-center justify-between mb-4">
                <span class="text-gray-400 text-sm">Member Since</span>
                <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-white">{{ $tenant->created_at->format('M d, Y') }}</p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="glass-card p-6 rounded-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Total Leads</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['total_leads'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="glass-card p-6 rounded-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Total Chats</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['total_chats'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="glass-card p-6 rounded-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">AI Agents</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['ai_agents'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="glass-card p-6 rounded-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-orange-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Workflows</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['workflows'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Bot Flowchart (n8n Style) -->
    <div class="glass-card p-6 rounded-xl mb-8">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                </svg>
                Bot Configuration Flowchart
            </h3>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-4 text-sm">
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-green-500"></span>
                        <span class="text-gray-400">Connected</span>
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                        <span class="text-gray-400">Disconnected</span>
                    </span>
                </div>
                <button onclick="runFlowTest()" id="testFlowBtn"
                    class="px-4 py-2 bg-purple-500/20 text-purple-400 rounded-lg hover:bg-purple-500/30 transition-colors text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    Run Flow Test
                </button>
            </div>
        </div>

        <!-- Flowchart Canvas -->
        <div id="flowchartCanvas" class="relative bg-gray-900/50 rounded-xl border border-gray-700 overflow-hidden"
            style="height: 520px;">
            <!-- SVG for connection lines -->
            <svg id="connectionsSvg" class="absolute inset-0 w-full h-full pointer-events-none" style="z-index: 1;">
                <defs>
                    <linearGradient id="activeGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" style="stop-color:#10b981;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#6366f1;stop-opacity:1" />
                    </linearGradient>
                    <linearGradient id="inactiveGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" style="stop-color:#6b7280;stop-opacity:0.5" />
                        <stop offset="100%" style="stop-color:#4b5563;stop-opacity:0.5" />
                    </linearGradient>
                </defs>
            </svg>

            <!-- Nodes will be dynamically inserted here -->
            <div id="nodesContainer" class="absolute inset-0" style="z-index: 2;"></div>

            <!-- Loading overlay -->
            <div id="flowchartLoading" class="absolute inset-0 flex items-center justify-center bg-gray-900/80">
                <div class="text-center">
                    <svg class="w-8 h-8 text-purple-400 animate-spin mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <p class="text-gray-400 text-sm">Loading flowchart...</p>
                </div>
            </div>
        </div>

        <!-- Test Results Section (Below Flowchart) -->
        <div id="testResultsSection" class="hidden mt-4 pt-4 border-t border-gray-700">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-white font-medium flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Test Results
                </h4>
                <span id="testSummaryBadge" class="px-3 py-1 rounded-full text-sm font-medium"></span>
            </div>
            <div id="testResultsContent" class="space-y-2 max-h-48 overflow-y-auto"></div>
        </div>

        <!-- Summary Bar -->
        <div id="flowchartSummary" class="mt-4 pt-4 border-t border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-gray-400 text-sm">Configuration Status:</span>
                <span id="configStatusBadge"
                    class="px-3 py-1 rounded-full text-sm font-medium bg-gray-700/50 text-gray-400">Loading...</span>
            </div>
            <div id="configStatusText" class="text-sm text-gray-500">Analyzing configuration...</div>
        </div>
    </div>

    <!-- Test Results Modal -->
    <div id="testResultsModal"
        class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="glass-card p-6 rounded-2xl w-full max-w-2xl mx-4 max-h-[80vh] overflow-hidden flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    Bot Flow Test Results
                </h3>
                <button onclick="closeTestModal()" class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Summary Cards -->
            <div id="modalSummaryCards" class="grid grid-cols-3 gap-4 mb-4"></div>

            <!-- Results List -->
            <div class="flex-1 overflow-y-auto space-y-3" id="modalResultsList"></div>

            <div class="mt-4 pt-4 border-t border-gray-700 flex justify-end">
                <button onclick="closeTestModal()"
                    class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Node Details Modal -->
    <div id="nodeDetailsModal"
        class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="glass-card p-6 rounded-2xl w-full max-w-lg mx-4 max-h-[80vh] overflow-hidden flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 id="nodeDetailsTitle" class="text-xl font-bold text-white flex items-center gap-2">
                    <span id="nodeDetailsIcon" class="text-2xl"></span>
                    <span id="nodeDetailsTitleText">Node Details</span>
                </h3>
                <button onclick="closeNodeDetailsModal()" class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Status Badge -->
            <div id="nodeDetailsStatus" class="mb-4"></div>

            <!-- Fields List -->
            <div class="flex-1 overflow-y-auto space-y-2" id="nodeDetailsFields"></div>

            <!-- Nodes List (for flowchart) -->
            <div id="nodeDetailsList" class="hidden mt-4 pt-4 border-t border-gray-700">
                <h4 class="text-white font-medium mb-2">Individual Nodes:</h4>
                <div id="nodeDetailsNodesContainer" class="space-y-2 max-h-40 overflow-y-auto"></div>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-700 flex justify-end">
                <button onclick="closeNodeDetailsModal()"
                    class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

    <style>
        .flowchart-node {
            position: absolute;
            width: 140px;
            padding: 12px;
            border-radius: 12px;
            cursor: grab;
            transition: box-shadow 0.3s, transform 0.2s;
            user-select: none;
            z-index: 10;
        }

        .flowchart-node:active {
            cursor: grabbing;
            transform: scale(1.05);
        }

        .flowchart-node.connected {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(16, 185, 129, 0.05) 100%);
            border: 2px solid rgba(16, 185, 129, 0.5);
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.2);
        }

        .flowchart-node.disconnected {
            background: linear-gradient(135deg, rgba(234, 179, 8, 0.15) 0%, rgba(234, 179, 8, 0.05) 100%);
            border: 2px solid rgba(234, 179, 8, 0.3);
        }

        .flowchart-node:hover {
            box-shadow: 0 0 30px rgba(139, 92, 246, 0.3);
        }

        .node-icon {
            font-size: 24px;
            margin-bottom: 4px;
        }

        .node-label {
            font-size: 12px;
            font-weight: 600;
            color: white;
            margin-bottom: 2px;
        }

        .node-subtitle {
            font-size: 10px;
            color: #9ca3af;
        }

        .node-status-dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .connection-line {
            fill: none;
            stroke-width: 2;
            stroke-dasharray: 8, 4;
            animation: flowAnimation 1s linear infinite;
        }

        .connection-line.active {
            stroke: url(#activeGradient);
        }

        .connection-line.inactive {
            stroke: url(#inactiveGradient);
            stroke-dasharray: 4, 4;
            animation: none;
        }

        @keyframes flowAnimation {
            0% {
                stroke-dashoffset: 12;
            }

            100% {
                stroke-dashoffset: 0;
            }
        }

        .pulse-dot {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }
    </style>

    <script>
        let flowchartData = null;
        let isDragging = false;
        let dragNode = null;
        let dragOffset = { x: 0, y: 0 };

        document.addEventListener('DOMContentLoaded', function () {
            loadFlowchartData();
        });

        async function loadFlowchartData() {
            try {
                const response = await fetch('{{ route("superadmin.admins.flowchart-data", $tenant) }}');
                flowchartData = await response.json();
                renderFlowchart();
                document.getElementById('flowchartLoading').classList.add('hidden');
            } catch (error) {
                console.error('Error loading flowchart:', error);
                document.getElementById('flowchartLoading').innerHTML = '<p class="text-red-400">Error loading flowchart</p>';
            }
        }

        function renderFlowchart() {
            const container = document.getElementById('nodesContainer');
            const svg = document.getElementById('connectionsSvg');

            container.innerHTML = '';

            // Clear existing paths
            const existingPaths = svg.querySelectorAll('path');
            existingPaths.forEach(p => p.remove());

            // Render nodes
            Object.values(flowchartData.nodes).forEach(node => {
                const nodeEl = createNodeElement(node);
                container.appendChild(nodeEl);
            });

            // Render connections
            renderConnections();
            updateSummary();
        }

        function createNodeElement(node) {
            const div = document.createElement('div');
            div.className = `flowchart-node ${node.connected ? 'connected' : 'disconnected'}`;
            div.id = `node-${node.id}`;
            div.dataset.nodeType = node.id;
            div.style.left = `${node.x}px`;
            div.style.top = `${node.y}px`;

            div.innerHTML = `
                    <div class="node-status-dot ${node.connected ? 'bg-green-500 pulse-dot' : 'bg-yellow-500'}"></div>
                    <div class="node-icon">${node.icon}</div>
                    <div class="node-label">${node.label}</div>
                    <div class="node-subtitle">${node.subtitle}</div>
                    <div class="text-xs text-center text-purple-400 mt-1 opacity-0 hover:opacity-100 transition-opacity">Click for details</div>
                `;

            // Dragging functionality
            div.addEventListener('mousedown', startDrag);

            return div;
        }


        let dragStartPos = { x: 0, y: 0 };
        let hasDragged = false;

        function startDrag(e) {
            isDragging = true;
            hasDragged = false;
            dragNode = e.currentTarget;
            dragStartPos = { x: e.clientX, y: e.clientY };
            const rect = dragNode.getBoundingClientRect();
            const containerRect = document.getElementById('flowchartCanvas').getBoundingClientRect();
            dragOffset.x = e.clientX - rect.left;
            dragOffset.y = e.clientY - rect.top;
            dragNode.style.zIndex = 100;

            document.addEventListener('mousemove', doDrag);
            document.addEventListener('mouseup', stopDrag);
        }

        function doDrag(e) {
            if (!isDragging || !dragNode) return;

            // Check if actually moved (to distinguish from click)
            const dx = Math.abs(e.clientX - dragStartPos.x);
            const dy = Math.abs(e.clientY - dragStartPos.y);
            if (dx > 5 || dy > 5) {
                hasDragged = true;
            }

            const container = document.getElementById('flowchartCanvas');
            const containerRect = container.getBoundingClientRect();

            let newX = e.clientX - containerRect.left - dragOffset.x;
            let newY = e.clientY - containerRect.top - dragOffset.y;

            // Keep within bounds
            newX = Math.max(0, Math.min(newX, containerRect.width - 140));
            newY = Math.max(0, Math.min(newY, containerRect.height - 80));

            dragNode.style.left = `${newX}px`;
            dragNode.style.top = `${newY}px`;

            // Update node data
            const nodeId = dragNode.id.replace('node-', '');
            if (flowchartData.nodes[nodeId]) {
                flowchartData.nodes[nodeId].x = newX;
                flowchartData.nodes[nodeId].y = newY;
            }

            renderConnections();
        }

        function stopDrag() {
            if (dragNode) {
                dragNode.style.zIndex = 10;
                
                // If no drag happened, treat as click
                if (!hasDragged) {
                    const nodeType = dragNode.dataset.nodeType;
                    if (nodeType) {
                        showNodeDetails(nodeType);
                    }
                }
            }
            isDragging = false;
            hasDragged = false;
            dragNode = null;
            document.removeEventListener('mousemove', doDrag);
            document.removeEventListener('mouseup', stopDrag);
        }

        function renderConnections() {
            const svg = document.getElementById('connectionsSvg');
            const existingPaths = svg.querySelectorAll('path');
            existingPaths.forEach(p => p.remove());

            flowchartData.connections.forEach(conn => {
                const fromNode = flowchartData.nodes[conn.from];
                const toNode = flowchartData.nodes[conn.to];

                if (!fromNode || !toNode) return;

                const fromX = fromNode.x + 70; // Center of node
                const fromY = fromNode.y + 60; // Bottom of node
                const toX = toNode.x + 70;
                const toY = toNode.y;

                // Create curved path
                const midY = (fromY + toY) / 2;
                const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                path.setAttribute('d', `M ${fromX} ${fromY} C ${fromX} ${midY}, ${toX} ${midY}, ${toX} ${toY}`);
                path.classList.add('connection-line', conn.active ? 'active' : 'inactive');

                svg.appendChild(path);
            });
        }

        function updateSummary() {
            const connectedCount = Object.values(flowchartData.nodes).filter(n => n.connected).length;
            const totalCount = Object.values(flowchartData.nodes).length;

            const badge = document.getElementById('configStatusBadge');
            const text = document.getElementById('configStatusText');

            if (connectedCount >= 5) {
                badge.className = 'px-3 py-1 rounded-full text-sm font-medium bg-green-500/20 text-green-400';
                badge.textContent = `${connectedCount}/${totalCount} Ready`;
                text.textContent = '✓ Bot is fully configured';
            } else if (connectedCount >= 3) {
                badge.className = 'px-3 py-1 rounded-full text-sm font-medium bg-yellow-500/20 text-yellow-400';
                badge.textContent = `${connectedCount}/${totalCount} Ready`;
                text.textContent = '⚠ Partial configuration';
            } else {
                badge.className = 'px-3 py-1 rounded-full text-sm font-medium bg-red-500/20 text-red-400';
                badge.textContent = `${connectedCount}/${totalCount} Ready`;
                text.textContent = '✗ Setup required';
            }
        }

        async function runFlowTest() {
            const btn = document.getElementById('testFlowBtn');
            btn.disabled = true;
            btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Testing...';

            try {
                const response = await fetch('{{ route("superadmin.admins.test-bot-flow", $tenant) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const results = await response.json();
                showTestResults(results);
                showTestModal(results);
            } catch (error) {
                alert('Error running test: ' + error.message);
            }

            btn.disabled = false;
            btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg> Run Flow Test';
        }

        function showTestResults(results) {
            const section = document.getElementById('testResultsSection');
            const content = document.getElementById('testResultsContent');
            const badge = document.getElementById('testSummaryBadge');

            section.classList.remove('hidden');

            // Update badge
            if (results.valid) {
                badge.className = 'px-3 py-1 rounded-full text-sm font-medium bg-green-500/20 text-green-400';
                badge.textContent = '✓ All tests passed';
            } else {
                badge.className = 'px-3 py-1 rounded-full text-sm font-medium bg-red-500/20 text-red-400';
                badge.textContent = `✗ ${results.summary.failed} issues found`;
            }

            // Show inline results
            content.innerHTML = '';
            [...results.errors, ...results.warnings].slice(0, 3).forEach(item => {
                const div = document.createElement('div');
                div.className = `flex items-start gap-2 p-2 rounded-lg ${item.type === 'error' ? 'bg-red-500/10' : 'bg-yellow-500/10'}`;
                div.innerHTML = `
                        <span class="${item.type === 'error' ? 'text-red-400' : 'text-yellow-400'}">${item.type === 'error' ? '✗' : '⚠'}</span>
                        <div>
                            <span class="text-xs text-gray-500">${item.code}</span>
                            <p class="text-sm text-white">${item.message}</p>
                        </div>
                    `;
                content.appendChild(div);
            });

            if (results.errors.length + results.warnings.length > 3) {
                const more = document.createElement('p');
                more.className = 'text-gray-400 text-sm text-center';
                more.textContent = `+ ${results.errors.length + results.warnings.length - 3} more issues`;
                content.appendChild(more);
            }
        }

        function showTestModal(results) {
            const modal = document.getElementById('testResultsModal');
            const summaryCards = document.getElementById('modalSummaryCards');
            const resultsList = document.getElementById('modalResultsList');

            // Summary cards
            summaryCards.innerHTML = `
                    <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-green-400">${results.summary.passed}</div>
                        <div class="text-xs text-gray-400">Passed</div>
                    </div>
                    <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-yellow-400">${results.summary.warnings}</div>
                        <div class="text-xs text-gray-400">Warnings</div>
                    </div>
                    <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-red-400">${results.summary.failed}</div>
                        <div class="text-xs text-gray-400">Errors</div>
                    </div>
                `;

            // Results list
            resultsList.innerHTML = '';

            // Errors first
            results.errors.forEach(item => {
                resultsList.appendChild(createResultItem(item, 'error'));
            });

            // Warnings
            results.warnings.forEach(item => {
                resultsList.appendChild(createResultItem(item, 'warning'));
            });

            // Success
            results.success.forEach(item => {
                resultsList.appendChild(createResultItem(item, 'success'));
            });

            modal.classList.remove('hidden');
        }

        function createResultItem(item, type) {
            const div = document.createElement('div');
            const colors = {
                error: 'bg-red-500/10 border-red-500/30 text-red-400',
                warning: 'bg-yellow-500/10 border-yellow-500/30 text-yellow-400',
                success: 'bg-green-500/10 border-green-500/30 text-green-400'
            };
            const icons = {
                error: '✗',
                warning: '⚠',
                success: '✓'
            };

            div.className = `flex items-start gap-3 p-3 rounded-lg border ${colors[type]}`;
            div.innerHTML = `
                    <span class="text-lg">${icons[type]}</span>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-xs bg-gray-700/50 px-2 py-0.5 rounded text-gray-400">${item.code}</span>
                        </div>
                        <p class="text-sm text-white mt-1">${item.message}</p>
                    </div>
                `;
            return div;
        }

        function closeTestModal() {
            document.getElementById('testResultsModal').classList.add('hidden');
        }

        async function showNodeDetails(nodeType) {
            const modal = document.getElementById('nodeDetailsModal');
            const icon = document.getElementById('nodeDetailsIcon');
            const title = document.getElementById('nodeDetailsTitleText');
            const status = document.getElementById('nodeDetailsStatus');
            const fieldsContainer = document.getElementById('nodeDetailsFields');
            const nodesList = document.getElementById('nodeDetailsList');
            const nodesContainer = document.getElementById('nodeDetailsNodesContainer');

            // Show loading
            title.textContent = 'Loading...';
            fieldsContainer.innerHTML = '<div class="text-center text-gray-400">Loading details...</div>';
            modal.classList.remove('hidden');

            try {
                const response = await fetch(`{{ url('superadmin/admins/' . $tenant->id . '/node-details') }}/${nodeType}`);
                const data = await response.json();

                if (data.error) {
                    fieldsContainer.innerHTML = `<div class="text-center text-red-400">${data.error}</div>`;
                    return;
                }

                // Set header
                icon.textContent = data.icon;
                title.textContent = data.title;

                // Set status badge
                status.innerHTML = `
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm ${data.status === 'connected' ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400'}">
                        <span class="w-2 h-2 rounded-full ${data.status === 'connected' ? 'bg-green-400' : 'bg-yellow-400'}"></span>
                        ${data.status === 'connected' ? 'Connected' : 'Disconnected'}
                    </span>
                `;

                // Render fields
                fieldsContainer.innerHTML = '';
                data.fields.forEach(field => {
                    const div = document.createElement('div');
                    div.className = `flex items-center justify-between p-3 rounded-lg border ${field.connected ? 'bg-green-500/5 border-green-500/20' : 'bg-yellow-500/5 border-yellow-500/20'}`;
                    
                    let extras = '';
                    if (field.inFlowchart !== undefined) {
                        extras += field.inFlowchart ? '<span class="text-xs bg-blue-500/20 text-blue-400 px-2 py-0.5 rounded">In Flowchart</span>' : '<span class="text-xs bg-gray-500/20 text-gray-400 px-2 py-0.5 rounded">Not in Flowchart</span>';
                    }
                    if (field.matchesCatalogue !== undefined) {
                        extras += field.matchesCatalogue ? '<span class="text-xs bg-purple-500/20 text-purple-400 px-2 py-0.5 rounded ml-1">Matches Catalogue</span>' : '';
                    }
                    if (field.active !== undefined) {
                        extras += field.active ? '' : '<span class="text-xs bg-red-500/20 text-red-400 px-2 py-0.5 rounded ml-1">Inactive</span>';
                    }

                    div.innerHTML = `
                        <div class="flex-1">
                            <div class="text-white font-medium text-sm">${field.label}</div>
                            <div class="text-gray-400 text-xs">${field.value}</div>
                            ${extras ? `<div class="mt-1">${extras}</div>` : ''}
                        </div>
                        <span class="w-3 h-3 rounded-full ${field.connected ? 'bg-green-500' : 'bg-yellow-500'}"></span>
                    `;
                    fieldsContainer.appendChild(div);
                });

                // Render individual nodes if available
                if (data.nodes && data.nodes.length > 0) {
                    nodesList.classList.remove('hidden');
                    nodesContainer.innerHTML = '';
                    data.nodes.forEach(node => {
                        const hasIssue = !node.hasOutgoing || (!node.hasIncoming && node.type !== 'start');
                        const div = document.createElement('div');
                        div.className = `flex items-center justify-between p-2 rounded ${hasIssue ? 'bg-yellow-500/10' : 'bg-gray-700/30'}`;
                        div.innerHTML = `
                            <div>
                                <span class="text-white text-sm">${node.label}</span>
                                <span class="text-xs text-gray-500 ml-2">(${node.type})</span>
                            </div>
                            <div class="flex gap-1">
                                ${node.hasIncoming || node.type === 'start' ? '<span class="text-xs bg-green-500/20 text-green-400 px-1.5 py-0.5 rounded">→In</span>' : '<span class="text-xs bg-red-500/20 text-red-400 px-1.5 py-0.5 rounded">No In</span>'}
                                ${node.hasOutgoing ? '<span class="text-xs bg-green-500/20 text-green-400 px-1.5 py-0.5 rounded">Out→</span>' : '<span class="text-xs bg-red-500/20 text-red-400 px-1.5 py-0.5 rounded">No Out</span>'}
                                ${node.fieldLinked ? '<span class="text-xs bg-blue-500/20 text-blue-400 px-1.5 py-0.5 rounded">Field</span>' : ''}
                            </div>
                        `;
                        nodesContainer.appendChild(div);
                    });
                } else {
                    nodesList.classList.add('hidden');
                }

            } catch (error) {
                fieldsContainer.innerHTML = `<div class="text-center text-red-400">Error loading details: ${error.message}</div>`;
            }
        }

        function closeNodeDetailsModal() {
            document.getElementById('nodeDetailsModal').classList.add('hidden');
        }
    </script>



    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Company Information -->
        <div class="glass-card p-6 rounded-xl">
            <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Company Information
            </h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center py-3 border-b border-gray-700">
                    <span class="text-gray-400">Company Name</span>
                    <span class="text-white font-medium">{{ $tenant->name }}</span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-gray-700">
                    <span class="text-gray-400">Email</span>
                    <span class="text-white font-medium">{{ $tenant->email }}</span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-gray-700">
                    <span class="text-gray-400">Phone</span>
                    <span class="text-white font-medium">{{ $tenant->phone ?? 'Not provided' }}</span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-gray-700">
                    <span class="text-gray-400">Domain</span>
                    <span class="text-white font-medium">{{ $tenant->domain ?? 'Not configured' }}</span>
                </div>
                <div class="flex justify-between items-center py-3">
                    <span class="text-gray-400">Address</span>
                    <span class="text-white font-medium text-right max-w-xs">{{ $tenant->address ?? 'Not provided' }}</span>
                </div>
            </div>
        </div>

        <!-- Admin Users -->
        <div class="glass-card p-6 rounded-xl">
            <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Admin Users
            </h3>
            <div class="space-y-4">
                @forelse($tenant->admins ?? [] as $admin)
                    <div class="flex items-center justify-between py-3 border-b border-gray-700 last:border-0">
                        <div class="flex items-center space-x-3">
                            <div
                                class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold">
                                {{ strtoupper(substr($admin->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-white font-medium">{{ $admin->name }}</p>
                                <p class="text-gray-400 text-sm">{{ $admin->email }}</p>
                            </div>
                        </div>
                        <span
                            class="px-2 py-1 rounded text-xs {{ $admin->status === 'active' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                            {{ ucfirst($admin->status ?? 'active') }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-400 text-center py-4">No admin users found</p>
                @endforelse
            </div>
        </div>

        <!-- Credit Usage -->
        <div class="glass-card p-6 rounded-xl">
            <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Credit Information
            </h3>
            @php
                $credits = $tenant->credits;
                $totalCredits = $credits->total_credits ?? 0;
                $usedCredits = $credits->used_credits ?? 0;
                $availableCredits = $credits->available_credits ?? 0;
                $usagePercentage = $totalCredits > 0 ? ($usedCredits / $totalCredits) * 100 : 0;
            @endphp
            <div class="space-y-4">
                <div class="flex justify-between items-center py-3 border-b border-gray-700">
                    <span class="text-gray-400">Total Credits</span>
                    <span class="text-white font-bold text-xl">{{ number_format($totalCredits) }}</span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-gray-700">
                    <span class="text-gray-400">Used Credits</span>
                    <span class="text-red-400 font-medium">{{ number_format($usedCredits) }}</span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-gray-700">
                    <span class="text-gray-400">Available Credits</span>
                    <span class="text-green-400 font-bold text-xl">{{ number_format($availableCredits) }}</span>
                </div>
                <div class="pt-2">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-gray-400">Usage</span>
                        <span class="text-white">{{ number_format($usagePercentage, 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-3">
                        <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-3 rounded-full transition-all duration-500"
                            style="width: {{ min($usagePercentage, 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="glass-card p-6 rounded-xl">
            <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                Recent Payments
            </h3>
            <div class="space-y-4">
                @forelse($tenant->payments->take(5) ?? [] as $payment)
                    <div class="flex items-center justify-between py-3 border-b border-gray-700 last:border-0">
                        <div>
                            <p class="text-white font-medium">₹{{ number_format($payment->amount) }}</p>
                            <p class="text-gray-400 text-sm">{{ $payment->created_at->format('M d, Y') }}</p>
                        </div>
                        <span
                            class="px-2 py-1 rounded text-xs {{ $payment->status === 'completed' ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400' }}">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-400 text-center py-4">No payments found</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- AI System Prompt Preview -->
    <div class="mt-8 glass-card p-6 rounded-xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                AI System Prompt Preview
            </h3>
            <div class="flex items-center gap-3">
                <button onclick="loadPrompt()" id="loadBtn"
                    class="px-4 py-2 bg-blue-500/20 text-blue-400 rounded-lg hover:bg-blue-500/30 transition-colors text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Load Full Prompt
                </button>
                <button onclick="copyPrompt()" id="copyBtn"
                    class="hidden px-4 py-2 bg-gray-500/20 text-gray-400 rounded-lg hover:bg-gray-500/30 transition-colors text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                    </svg>
                    Copy
                </button>
                <a href="{{ route('superadmin.admins.edit', $tenant) }}"
                    class="text-primary-400 hover:text-primary-300 text-sm flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Prompt
                </a>
            </div>
        </div>

        <!-- Catalogue Info Card -->
        <div id="catalogueInfo" class="hidden mb-4">
            <h4 class="text-white font-medium mb-3">📦 Catalogue Data for This Admin</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-gray-700/30 rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold text-blue-400" id="totalProducts">0</div>
                    <div class="text-gray-400 text-sm">Total Products</div>
                </div>
                <div class="bg-gray-700/30 rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold text-green-400" id="productTypes">0</div>
                    <div class="text-gray-400 text-sm">Product Types</div>
                </div>
                <div class="bg-gray-700/30 rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold text-purple-400" id="categories">0</div>
                    <div class="text-gray-400 text-sm">Categories</div>
                </div>
                <div class="bg-gray-700/30 rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold text-yellow-400" id="fieldRules">0</div>
                    <div class="text-gray-400 text-sm">Field Rules</div>
                </div>
            </div>
            <div id="productTypesList" class="mt-3 hidden">
                <span class="text-gray-400 text-sm">Product Types: </span>
                <div class="flex flex-wrap gap-2 mt-1" id="productTypesContainer"></div>
            </div>
        </div>

        <!-- Loading / Simple Prompt -->
        <div id="promptLoading">
            @if($tenant->ai_system_prompt)
                <div class="bg-black/30 rounded-xl p-4 max-h-64 overflow-y-auto">
                    <p class="text-gray-500 text-xs mb-2">Basic Prompt Preview (Click "Load Full Prompt" to see complete prompt
                        with catalogue data)</p>
                    <pre class="text-gray-300 text-sm whitespace-pre-wrap font-mono">{{ $tenant->ai_system_prompt }}</pre>
                </div>
            @else
                <div class="bg-black/20 rounded-xl p-6 text-center">
                    <p class="text-gray-400">No custom AI prompt configured for this admin.</p>
                    <a href="{{ route('superadmin.admins.edit', $tenant) }}"
                        class="text-primary-400 hover:underline text-sm mt-2 inline-block">Add Prompt →</a>
                </div>
            @endif
        </div>

        <!-- Full Prompt Content -->
        <pre id="promptContent"
            class="hidden bg-black/30 rounded-xl p-4 text-gray-300 text-sm overflow-x-auto whitespace-pre-wrap max-h-[500px] overflow-y-auto font-mono"></pre>

        <p class="text-gray-500 text-xs mt-2">This prompt is sent to AI with every message along with the catalogue data.
            Admin can also edit this in their Settings → AI Configuration.</p>
    </div>

    <script>
        async function loadPrompt() {
            const loadBtn = document.getElementById('loadBtn');
            const copyBtn = document.getElementById('copyBtn');
            const promptContent = document.getElementById('promptContent');
            const promptLoading = document.getElementById('promptLoading');
            const catalogueInfo = document.getElementById('catalogueInfo');

            loadBtn.disabled = true;
            loadBtn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg> Loading...';

            try {
                const response = await fetch('{{ route("superadmin.ai-config.prompt-preview.get") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ admin_id: {{ $tenant->id }} })
                });

                const data = await response.json();

                if (data.success) {
                    // Show prompt
                    promptContent.textContent = data.prompt;
                    promptContent.classList.remove('hidden');
                    promptLoading.classList.add('hidden');
                    copyBtn.classList.remove('hidden');

                    // Show catalogue info
                    catalogueInfo.classList.remove('hidden');
                    document.getElementById('totalProducts').textContent = data.catalogue_info.total_products || 0;
                    document.getElementById('productTypes').textContent = (data.catalogue_info.product_types || []).length;
                    document.getElementById('categories').textContent = (data.catalogue_info.categories || []).length;
                    document.getElementById('fieldRules').textContent = (data.context.field_rules || []).length;

                    // Show product types
                    const productTypes = data.catalogue_info.product_types || [];
                    if (productTypes.length > 0) {
                        document.getElementById('productTypesList').classList.remove('hidden');
                        document.getElementById('productTypesContainer').innerHTML = productTypes.map(t =>
                            `<span class="px-2 py-1 bg-blue-500/20 text-blue-300 rounded-full text-xs">${t}</span>`
                        ).join('');
                    }

                    loadBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Loaded';
                } else {
                    alert('Error: ' + (data.error || 'Failed to load prompt'));
                    loadBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg> Load Full Prompt';
                }
            } catch (error) {
                alert('Error: ' + error.message);
                loadBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg> Load Full Prompt';
            }

            loadBtn.disabled = false;
        }

        function copyPrompt() {
            const prompt = document.getElementById('promptContent').textContent;
            navigator.clipboard.writeText(prompt).then(() => {
                const copyBtn = document.getElementById('copyBtn');
                copyBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Copied!';
                setTimeout(() => {
                    copyBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg> Copy';
                }, 2000);
            });
        }
    </script>

    <!-- Quick Actions -->
    <div class="mt-8 glass-card p-6 rounded-xl">
        <h3 class="text-lg font-semibold text-white mb-6">Quick Actions</h3>
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('superadmin.admins.chats', $tenant) }}"
                class="bg-purple-500/20 hover:bg-purple-500/30 text-purple-400 px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                View Chats
            </a>
            <button onclick="document.getElementById('addCreditsModal').classList.remove('hidden')"
                class="bg-green-500/20 hover:bg-green-500/30 text-green-400 px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Credits
            </button>
            <button onclick="document.getElementById('resetPasswordModal').classList.remove('hidden')"
                class="bg-orange-500/20 hover:bg-orange-500/30 text-orange-400 px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                Reset Password
            </button>
            <a href="{{ route('superadmin.admins.edit', $tenant) }}"
                class="bg-blue-500/20 hover:bg-blue-500/30 text-blue-400 px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Details
            </a>
            <form action="{{ route('superadmin.admins.toggle-status', $tenant) }}" method="POST" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit"
                    class="{{ $tenant->is_active ? 'bg-red-500/20 hover:bg-red-500/30 text-red-400' : 'bg-green-500/20 hover:bg-green-500/30 text-green-400' }} px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                    {{ $tenant->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Add Credits Modal -->
    <div id="addCreditsModal"
        class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="glass-card p-8 rounded-2xl w-full max-w-md mx-4">
            <h3 class="text-xl font-bold text-white mb-6">Add Credits to {{ $tenant->name }}</h3>
            <form action="{{ route('superadmin.admins.add-credits', $tenant) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-300 text-sm font-medium mb-2">Amount of Credits</label>
                    <input type="number" name="credits" required min="1" class="form-input w-full"
                        placeholder="Enter credits amount">
                </div>
                <div class="mb-6">
                    <label class="block text-gray-300 text-sm font-medium mb-2">Reason (Optional)</label>
                    <input type="text" name="reason" class="form-input w-full" placeholder="e.g., Monthly top-up">
                </div>
                <div class="flex space-x-4">
                    <button type="button" onclick="document.getElementById('addCreditsModal').classList.add('hidden')"
                        class="flex-1 bg-gray-700 hover:bg-gray-600 text-white py-2 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 btn-gradient py-2 rounded-lg">
                        Add Credits
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div id="resetPasswordModal"
        class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="glass-card p-8 rounded-2xl w-full max-w-md mx-4">
            <h3 class="text-xl font-bold text-white mb-2">Reset Admin Password</h3>
            <p class="text-gray-400 text-sm mb-6">Set a new password for the admin's login account</p>
            <form action="{{ route('superadmin.admins.reset-password', $tenant) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-300 text-sm font-medium mb-2">Select Admin User</label>
                    <select name="admin_id" required class="form-select w-full"
                        style="background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(148, 163, 184, 0.2); color: #fff; padding: 0.75rem 1rem; border-radius: 0.5rem;">
                        @forelse($tenant->admins ?? [] as $admin)
                            <option value="{{ $admin->id }}" style="background: #1e293b; color: #fff;">{{ $admin->name }}
                                ({{ $admin->email }})</option>
                        @empty
                            <option value="" style="background: #1e293b; color: #fff;">No admin users found</option>
                        @endforelse
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-300 text-sm font-medium mb-2">New Password</label>
                    <input type="password" name="password" required minlength="6" class="form-input w-full"
                        placeholder="Enter new password"
                        style="background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(148, 163, 184, 0.2); color: #fff;">
                </div>
                <div class="mb-6">
                    <label class="block text-gray-300 text-sm font-medium mb-2">Confirm Password</label>
                    <input type="password" name="password_confirmation" required minlength="6" class="form-input w-full"
                        placeholder="Confirm new password"
                        style="background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(148, 163, 184, 0.2); color: #fff;">
                </div>
                <div class="flex space-x-4">
                    <button type="button" onclick="document.getElementById('resetPasswordModal').classList.add('hidden')"
                        class="flex-1 bg-gray-700 hover:bg-gray-600 text-white py-2 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 btn-gradient py-2 rounded-lg">
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection