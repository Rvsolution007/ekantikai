@extends('admin.layouts.app')

@section('title', 'Flowchart Builder')

@push('styles')
    <style>
        /* ==================== N8N-STYLE FLOWCHART EDITOR ==================== */

        /* Canvas Container */
        #flowchart-canvas {
            width: 100%;
            height: calc(100vh - 200px);
            min-height: 600px;
            background: #1a1a2e;
            background-image:
                linear-gradient(rgba(99, 102, 241, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99, 102, 241, 0.05) 1px, transparent 1px);
            background-size: 20px 20px;
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            cursor: grab;
        }

        #flowchart-canvas:active {
            cursor: grabbing;
        }

        /* SVG Layer for Connections */
        #connections-svg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        #connections-svg path {
            pointer-events: stroke;
            cursor: pointer;
        }

        #connections-svg path.connection-path {
            fill: none;
            stroke: #6366f1;
            stroke-width: 3;
            transition: stroke 0.2s, stroke-width 0.2s;
        }

        #connections-svg path.connection-path:hover {
            stroke: #a855f7;
            stroke-width: 4;
        }

        #connections-svg path.connection-temp {
            fill: none;
            stroke: #6366f1;
            stroke-width: 3;
            stroke-dasharray: 8, 4;
            opacity: 0.7;
        }

        /* Nodes Container */
        #nodes-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 2;
        }

        /* N8N Style Node */
        .flow-node {
            position: absolute;
            background: #262640;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            min-width: 160px;
            max-width: 200px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
            cursor: grab;
            user-select: none;
            z-index: 10;
            transition: box-shadow 0.2s, transform 0.1s;
        }

        .flow-node:hover {
            box-shadow: 0 8px 30px rgba(99, 102, 241, 0.3);
        }

        .flow-node.selected {
            border-color: #6366f1;
            box-shadow: 0 0 0 2px #6366f1, 0 8px 30px rgba(99, 102, 241, 0.4);
        }

        .flow-node.dragging {
            cursor: grabbing;
            z-index: 100;
            transform: scale(1.02);
        }

        .flow-node-inner {
            display: flex;
            align-items: stretch;
            min-height: 54px;
        }

        .flow-node-icon {
            width: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px 0 0 9px;
            font-size: 18px;
        }

        .flow-node-content {
            flex: 1;
            padding: 10px 12px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .flow-node-title {
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .flow-node-subtitle {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 2px;
        }

        /* Node Type Colors */
        .flow-node[data-type="start"] .flow-node-icon {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .flow-node[data-type="question"] .flow-node-icon {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
        }

        .flow-node[data-type="condition"] .flow-node-icon {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .flow-node[data-type="action"] .flow-node-icon {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }

        .flow-node[data-type="end"] .flow-node-icon {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        /* Connection Handles */
        .flow-handle {
            position: absolute;
            width: 16px;
            height: 16px;
            background: #1a1a2e;
            border: 3px solid #6366f1;
            border-radius: 50%;
            cursor: crosshair;
            z-index: 20;
            transition: all 0.2s;
        }

        .flow-handle:hover {
            background: #6366f1;
            transform: scale(1.3);
            box-shadow: 0 0 12px rgba(99, 102, 241, 0.8);
        }

        .flow-handle.handle-input {
            left: -8px;
            top: 50%;
            transform: translateY(-50%);
        }

        .flow-handle.handle-input:hover {
            transform: translateY(-50%) scale(1.3);
        }

        .flow-handle.handle-output {
            right: -8px;
            top: 50%;
            transform: translateY(-50%);
        }

        .flow-handle.handle-output:hover {
            transform: translateY(-50%) scale(1.3);
        }

        .flow-handle.connecting {
            background: #a855f7;
            border-color: #a855f7;
            box-shadow: 0 0 15px rgba(168, 85, 247, 0.9);
        }

        /* Connection Delete Button */
        .connection-delete {
            position: absolute;
            width: 20px;
            height: 20px;
            background: #ef4444;
            border: 2px solid #fff;
            border-radius: 50%;
            color: #fff;
            font-size: 12px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: all 0.2s;
            z-index: 50;
            pointer-events: auto;
        }

        .connection-delete:hover {
            background: #dc2626;
            transform: scale(1.2);
        }

        .connection-delete.visible {
            opacity: 1;
        }

        /* Sidebar Node Palette */
        .node-palette-item {
            cursor: grab;
            transition: all 0.2s;
            user-select: none;
        }

        .node-palette-item:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
        }

        .node-palette-item:active {
            cursor: grabbing;
        }

        /* Zoom Controls */
        .zoom-controls {
            position: absolute;
            bottom: 16px;
            right: 16px;
            display: flex;
            gap: 8px;
            z-index: 100;
        }

        .zoom-btn {
            width: 36px;
            height: 36px;
            background: rgba(38, 38, 64, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .zoom-btn:hover {
            background: #6366f1;
        }
    </style>
@endpush

@section('content')
    <div class="h-full flex flex-col" id="flowchart-app">
        <!-- Header -->
        <div class="glass rounded-2xl p-4 mb-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.workflow.fields.index') }}"
                        class="w-10 h-10 rounded-xl bg-white/5 hover:bg-white/10 flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-xl font-bold text-white flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                                </svg>
                            </div>
                            Flowchart Builder
                        </h1>
                        <p class="text-gray-400 text-sm mt-1">Design your chatbot conversation flow visually</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button onclick="flowchart.clearFlow()"
                        class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 text-sm flex items-center gap-2 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Clear
                    </button>
                    <button onclick="flowchart.saveFlow()" id="save-btn"
                        class="btn-primary px-6 py-2.5 rounded-xl text-white font-medium flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Flow
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex gap-4 flex-1 min-h-0">
            <!-- Left Sidebar - Node Palette -->
            <div class="w-64 glass rounded-2xl p-4 flex-shrink-0 overflow-y-auto">
                <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Drag Nodes</h3>

                <div class="space-y-3">
                    <div class="node-palette-item glass-light rounded-xl p-3" draggable="true" data-node-type="start">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center">
                                <span class="text-lg">▶️</span>
                            </div>
                            <div>
                                <div class="text-white font-medium text-sm">Start</div>
                                <div class="text-gray-500 text-xs">Entry point</div>
                            </div>
                        </div>
                    </div>

                    <div class="node-palette-item glass-light rounded-xl p-3" draggable="true" data-node-type="question">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-lg bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center">
                                <span class="text-lg">❓</span>
                            </div>
                            <div>
                                <div class="text-white font-medium text-sm">Question</div>
                                <div class="text-gray-500 text-xs">Ask the user</div>
                            </div>
                        </div>
                    </div>

                    <div class="node-palette-item glass-light rounded-xl p-3" draggable="true" data-node-type="condition">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-lg bg-gradient-to-br from-yellow-500 to-orange-500 flex items-center justify-center">
                                <span class="text-lg">⚡</span>
                            </div>
                            <div>
                                <div class="text-white font-medium text-sm">Condition</div>
                                <div class="text-gray-500 text-xs">Branch logic</div>
                            </div>
                        </div>
                    </div>

                    <div class="node-palette-item glass-light rounded-xl p-3" draggable="true" data-node-type="action">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-violet-600 flex items-center justify-center">
                                <span class="text-lg">⚙️</span>
                            </div>
                            <div>
                                <div class="text-white font-medium text-sm">Action</div>
                                <div class="text-gray-500 text-xs">Send message</div>
                            </div>
                        </div>
                    </div>

                    <div class="node-palette-item glass-light rounded-xl p-3" draggable="true" data-node-type="end">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-lg bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center">
                                <span class="text-lg">🏁</span>
                            </div>
                            <div>
                                <div class="text-white font-medium text-sm">End</div>
                                <div class="text-gray-500 text-xs">Complete flow</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-primary-500/10 rounded-xl border border-primary-500/20">
                    <h4 class="text-primary-400 font-medium text-sm mb-2">💡 Tips</h4>
                    <ul class="text-gray-400 text-xs space-y-1">
                        <li>• Drag nodes to canvas</li>
                        <li>• Click output → drag → input</li>
                        <li>• Double-click line to delete</li>
                        <li>• Del key removes selected</li>
                    </ul>
                </div>
            </div>

            <!-- Canvas Area -->
            <div class="flex-1 relative overflow-hidden rounded-2xl">
                <div id="flowchart-canvas">
                    <svg id="connections-svg"></svg>
                    <div id="nodes-container"></div>

                    <!-- Zoom Controls -->
                    <div class="zoom-controls">
                        <button class="zoom-btn" onclick="flowchart.zoomIn()" title="Zoom In">+</button>
                        <button class="zoom-btn" onclick="flowchart.zoomOut()" title="Zoom Out">−</button>
                        <button class="zoom-btn" onclick="flowchart.resetZoom()" title="Reset">⟲</button>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar - Properties -->
            <div class="w-80 glass rounded-2xl p-4 flex-shrink-0 overflow-y-auto">
                <div id="node-properties" style="display: none;">
                    <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Node Properties</h3>
                    <div class="mb-4">
                        <span id="node-type-badge"
                            class="px-3 py-1 rounded-full text-xs font-medium bg-primary-500/20 text-primary-400"></span>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm text-gray-400 mb-2">Label</label>
                        <input type="text" id="node-label"
                            class="w-full px-3 py-2 rounded-lg text-white text-sm bg-dark-300 border border-white/10 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    </div>

                    <!-- Question Node Fields -->
                    <div class="mb-4" id="display-name-wrapper" style="display: none;">
                        <label class="block text-sm text-gray-400 mb-2">Select Question</label>
                        <select id="node-display-name"
                            class="w-full px-3 py-2 rounded-lg text-white text-sm bg-dark-300 border border-white/10 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            <option value="">-- Select a Question --</option>
                            <optgroup label="📦 Product Questions">
                                @foreach($productQuestions as $q)
                                    <option value="product_{{ $q->id }}" data-type="product" data-id="{{ $q->id }}"
                                        data-field-name="{{ $q->field_name }}" data-display-name="{{ $q->display_name }}">
                                        {{ $q->display_name }}
                                    </option>
                                @endforeach
                            </optgroup>
                            <optgroup label="🌐 Global Questions">
                                @foreach($globalQuestions as $q)
                                    <option value="global_{{ $q->id }}" data-type="global" data-id="{{ $q->id }}"
                                        data-field-name="{{ $q->field_name }}" data-display-name="{{ $q->display_name }}">
                                        {{ $q->display_name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>

                    <div class="mb-4" id="question-template-wrapper" style="display: none;">
                        <label class="block text-sm text-gray-400 mb-2">📝 Ask Question Format</label>
                        <textarea id="node-question-template" rows="3"
                            class="w-full px-3 py-2 rounded-lg text-white text-sm bg-dark-300 border border-white/10 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                            placeholder="e.g., XYZ product confirm, ab konsa Size chahiye?"></textarea>
                    </div>

                    <div class="mb-4" id="is-required-wrapper" style="display: none;">
                        <label class="block text-sm text-gray-400 mb-2">Field Type</label>
                        <select id="node-is-required"
                            class="w-full px-3 py-2 rounded-lg text-white text-sm bg-dark-300 border border-white/10">
                            <option value="1">✅ Required</option>
                            <option value="0">⏭️ Optional</option>
                        </select>
                    </div>

                    <div class="mb-4" id="lead-status-wrapper" style="display: none;">
                        <label class="block text-sm text-gray-400 mb-2">🎯 Lead Status</label>
                        <select id="node-lead-status"
                            class="w-full px-3 py-2 rounded-lg text-white text-sm bg-dark-300 border border-white/10">
                            <option value="">-- No status change --</option>
                            @foreach($leadStatuses ?? [] as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button onclick="flowchart.deleteSelectedNode()"
                        class="w-full px-4 py-2 rounded-lg bg-red-500/20 hover:bg-red-500/30 text-red-400 text-sm">
                        Delete Node
                    </button>
                </div>

                <div id="no-selection" class="flex flex-col items-center justify-center h-full text-center py-12">
                    <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5" />
                        </svg>
                    </div>
                    <h4 class="text-white font-medium mb-2">No Node Selected</h4>
                    <p class="text-gray-500 text-sm">Click on a node to edit</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // ==================== N8N-STYLE FLOWCHART BUILDER ====================
        const flowchart = {
            // State
            nodes: new Map(),
            connections: [],
            selectedNode: null,
            nodeIdCounter: 1,

            // Dragging state
            isDragging: false,
            dragNode: null,
            dragOffset: { x: 0, y: 0 },

            // Connection drawing state
            isConnecting: false,
            connectionStart: null,
            tempConnection: null,

            // Pan/Zoom state
            zoom: 1,
            panX: 0,
            panY: 0,
            isPanning: false,
            panStart: { x: 0, y: 0 },

            // DOM references
            canvas: null,
            svg: null,
            nodesContainer: null,

            // Node configs
            nodeConfig: {
                start: { inputs: 0, outputs: 1, icon: '▶', subtitle: 'Entry point' },
                question: { inputs: 1, outputs: 1, icon: '❓', subtitle: 'Ask user' },
                condition: { inputs: 1, outputs: 2, icon: '⚡', subtitle: 'Branch logic' },
                action: { inputs: 1, outputs: 1, icon: '⚙', subtitle: 'Execute task' },
                end: { inputs: 1, outputs: 0, icon: '🏁', subtitle: 'Complete flow' }
            },

            // Initialize
            init() {
                this.canvas = document.getElementById('flowchart-canvas');
                this.svg = document.getElementById('connections-svg');
                this.nodesContainer = document.getElementById('nodes-container');

                this.setupEventListeners();
                this.loadFlow();

                console.log('Flowchart Builder initialized!');
            },

            setupEventListeners() {
                // Global mouse events for dragging and connecting
                document.addEventListener('mousemove', (e) => this.onMouseMove(e));
                document.addEventListener('mouseup', (e) => this.onMouseUp(e));

                // Canvas events
                this.canvas.addEventListener('mousedown', (e) => this.onCanvasMouseDown(e));
                this.canvas.addEventListener('dragover', (e) => e.preventDefault());
                this.canvas.addEventListener('drop', (e) => this.onDrop(e));

                // Palette drag events
                document.querySelectorAll('.node-palette-item').forEach(item => {
                    item.addEventListener('dragstart', (e) => {
                        e.dataTransfer.setData('node-type', item.dataset.nodeType);
                    });
                });

                // Keyboard events
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Delete' && this.selectedNode) {
                        this.deleteSelectedNode();
                    }
                    if (e.key === 'Escape') {
                        this.cancelConnecting();
                    }
                });

                // Property panel events
                document.getElementById('node-label').addEventListener('input', (e) => this.updateNodeLabel(e.target.value));
                document.getElementById('node-display-name').addEventListener('change', (e) => this.updateNodeQuestion(e.target));
                document.getElementById('node-question-template').addEventListener('input', (e) => this.updateNodeTemplate(e.target.value));
                document.getElementById('node-is-required').addEventListener('change', (e) => this.updateNodeRequired(e.target.value));
                document.getElementById('node-lead-status').addEventListener('change', (e) => this.updateNodeLeadStatus(e.target.value));
            },

            // ==================== NODE MANAGEMENT ====================

            createNode(type, x, y, data = {}) {
                const id = data.dbId || 'node_' + this.nodeIdCounter++;
                const config = this.nodeConfig[type];
                const label = data.label || type.charAt(0).toUpperCase() + type.slice(1);

                const node = {
                    id,
                    type,
                    x,
                    y,
                    label,
                    data: data.config || {},
                    isRequired: data.is_required !== undefined ? data.is_required : true
                };

                this.nodes.set(id, node);
                this.renderNode(node);

                return id;
            },

            renderNode(node) {
                const config = this.nodeConfig[node.type];

                const el = document.createElement('div');
                el.className = 'flow-node';
                el.id = 'node-' + node.id;
                el.dataset.type = node.type;
                el.dataset.nodeId = node.id;
                el.style.left = node.x + 'px';
                el.style.top = node.y + 'px';

                el.innerHTML = `
                <div class="flow-node-inner">
                    <div class="flow-node-icon">${config.icon}</div>
                    <div class="flow-node-content">
                        <div class="flow-node-title">${node.label}</div>
                        <div class="flow-node-subtitle">${config.subtitle}</div>
                    </div>
                </div>
                ${config.inputs > 0 ? '<div class="flow-handle handle-input" data-handle="input"></div>' : ''}
                ${config.outputs > 0 ? '<div class="flow-handle handle-output" data-handle="output"></div>' : ''}
            `;

                // Node drag events
                el.addEventListener('mousedown', (e) => this.onNodeMouseDown(e, node.id));
                el.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.selectNode(node.id);
                });

                // Handle events
                const inputHandle = el.querySelector('.handle-input');
                const outputHandle = el.querySelector('.handle-output');

                if (outputHandle) {
                    outputHandle.addEventListener('mousedown', (e) => {
                        e.stopPropagation();
                        this.startConnecting(node.id, 'output', e);
                    });
                }

                if (inputHandle) {
                    inputHandle.addEventListener('mouseup', (e) => {
                        e.stopPropagation();
                        this.completeConnection(node.id, 'input');
                    });
                    inputHandle.addEventListener('mouseenter', () => {
                        if (this.isConnecting) inputHandle.classList.add('connecting');
                    });
                    inputHandle.addEventListener('mouseleave', () => {
                        inputHandle.classList.remove('connecting');
                    });
                }

                this.nodesContainer.appendChild(el);
            },

            // ==================== NODE DRAGGING ====================

            onNodeMouseDown(e, nodeId) {
                if (e.target.classList.contains('flow-handle')) return;

                e.preventDefault();
                e.stopPropagation();

                const node = this.nodes.get(nodeId);
                const nodeEl = document.getElementById('node-' + nodeId);

                this.isDragging = true;
                this.dragNode = nodeId;
                this.dragOffset = {
                    x: e.clientX - node.x,
                    y: e.clientY - node.y
                };

                nodeEl.classList.add('dragging');
                this.selectNode(nodeId);
            },

            onMouseMove(e) {
                if (this.isDragging && this.dragNode) {
                    const node = this.nodes.get(this.dragNode);
                    const nodeEl = document.getElementById('node-' + this.dragNode);
                    const rect = this.canvas.getBoundingClientRect();

                    node.x = e.clientX - this.dragOffset.x;
                    node.y = e.clientY - this.dragOffset.y;

                    nodeEl.style.left = node.x + 'px';
                    nodeEl.style.top = node.y + 'px';

                    // Real-time connection update (like n8n)
                    this.renderConnections();
                }

                if (this.isConnecting) {
                    this.updateTempConnection(e);
                }
            },

            onMouseUp(e) {
                if (this.isDragging && this.dragNode) {
                    const nodeEl = document.getElementById('node-' + this.dragNode);
                    nodeEl.classList.remove('dragging');
                }

                this.isDragging = false;
                this.dragNode = null;

                if (this.isConnecting && !e.target.classList.contains('handle-input')) {
                    this.cancelConnecting();
                }
            },

            // ==================== CONNECTIONS ====================

            startConnecting(nodeId, handleType, e) {
                this.isConnecting = true;
                this.connectionStart = { nodeId, handleType };

                const nodeEl = document.getElementById('node-' + nodeId);
                const handle = nodeEl.querySelector('.handle-output');
                const rect = handle.getBoundingClientRect();
                const canvasRect = this.canvas.getBoundingClientRect();

                this.connectionStart.x = rect.left + rect.width / 2 - canvasRect.left;
                this.connectionStart.y = rect.top + rect.height / 2 - canvasRect.top;

                // Create temp connection path
                this.tempConnection = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                this.tempConnection.classList.add('connection-temp');
                this.svg.appendChild(this.tempConnection);
            },

            updateTempConnection(e) {
                if (!this.tempConnection || !this.connectionStart) return;

                const canvasRect = this.canvas.getBoundingClientRect();
                const endX = e.clientX - canvasRect.left;
                const endY = e.clientY - canvasRect.top;

                const path = this.createBezierPath(
                    this.connectionStart.x, this.connectionStart.y,
                    endX, endY
                );
                this.tempConnection.setAttribute('d', path);
            },

            completeConnection(targetNodeId, handleType) {
                if (!this.isConnecting || !this.connectionStart) return;

                const sourceId = this.connectionStart.nodeId;

                // Prevent self-connection
                if (sourceId === targetNodeId) {
                    this.cancelConnecting();
                    return;
                }

                // Prevent duplicate connections
                const exists = this.connections.some(c => c.source === sourceId && c.target === targetNodeId);
                if (exists) {
                    this.cancelConnecting();
                    return;
                }

                this.connections.push({
                    id: 'conn_' + Date.now(),
                    source: sourceId,
                    target: targetNodeId,
                    sourceHandle: 'output_1',
                    targetHandle: 'input_1'
                });

                this.cancelConnecting();
                this.renderConnections();
                this.showToast('Connection created!', 'success');
            },

            cancelConnecting() {
                this.isConnecting = false;
                this.connectionStart = null;

                if (this.tempConnection) {
                    this.tempConnection.remove();
                    this.tempConnection = null;
                }

                document.querySelectorAll('.flow-handle.connecting').forEach(h => h.classList.remove('connecting'));
            },

            deleteConnection(connId) {
                this.connections = this.connections.filter(c => c.id !== connId);
                this.renderConnections();
                this.showToast('Connection deleted!', 'success');
            },

            // ==================== RENDERING CONNECTIONS ====================

            renderConnections() {
                // Clear existing connections and delete buttons
                this.svg.querySelectorAll('.connection-path').forEach(el => el.remove());
                document.querySelectorAll('.connection-delete-btn').forEach(el => el.remove());

                this.connections.forEach(conn => {
                    const sourceNode = this.nodes.get(conn.source);
                    const targetNode = this.nodes.get(conn.target);

                    if (!sourceNode || !targetNode) return;

                    const sourceEl = document.getElementById('node-' + conn.source);
                    const targetEl = document.getElementById('node-' + conn.target);

                    if (!sourceEl || !targetEl) return;

                    const sourceHandle = sourceEl.querySelector('.handle-output');
                    const targetHandle = targetEl.querySelector('.handle-input');

                    if (!sourceHandle || !targetHandle) return;

                    const canvasRect = this.canvas.getBoundingClientRect();
                    const sourceRect = sourceHandle.getBoundingClientRect();
                    const targetRect = targetHandle.getBoundingClientRect();

                    const x1 = sourceRect.left + sourceRect.width / 2 - canvasRect.left;
                    const y1 = sourceRect.top + sourceRect.height / 2 - canvasRect.top;
                    const x2 = targetRect.left + targetRect.width / 2 - canvasRect.left;
                    const y2 = targetRect.top + targetRect.height / 2 - canvasRect.top;

                    // Create path
                    const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                    path.classList.add('connection-path');
                    path.setAttribute('d', this.createBezierPath(x1, y1, x2, y2));
                    path.dataset.connId = conn.id;
                    
                    // Calculate midpoint for delete button
                    const midX = (x1 + x2) / 2;
                    const midY = (y1 + y2) / 2;
                    
                    // Create X delete button (n8n style)
                    const deleteBtn = document.createElement('div');
                    deleteBtn.className = 'connection-delete-btn';
                    deleteBtn.innerHTML = '✕';
                    deleteBtn.style.cssText = `
                        position: absolute;
                        left: ${midX}px;
                        top: ${midY}px;
                        width: 22px;
                        height: 22px;
                        background: #ef4444;
                        border: 2px solid #fff;
                        border-radius: 50%;
                        color: #fff;
                        font-size: 12px;
                        font-weight: bold;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                        opacity: 0;
                        transform: translate(-50%, -50%);
                        transition: all 0.15s ease;
                        z-index: 100;
                        pointer-events: auto;
                    `;
                    deleteBtn.dataset.connId = conn.id;
                    
                    // Show button on hover
                    const showBtn = () => { deleteBtn.style.opacity = '1'; };
                    const hideBtn = () => { deleteBtn.style.opacity = '0'; };
                    
                    path.addEventListener('mouseenter', showBtn);
                    path.addEventListener('mouseleave', hideBtn);
                    deleteBtn.addEventListener('mouseenter', showBtn);
                    deleteBtn.addEventListener('mouseleave', hideBtn);
                    
                    // Delete on click
                    deleteBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.deleteConnection(conn.id);
                    });

                    this.svg.appendChild(path);
                    this.nodesContainer.appendChild(deleteBtn);
                });
            },

            createBezierPath(x1, y1, x2, y2) {
                const dx = Math.abs(x2 - x1) * 0.5;
                const controlX1 = x1 + dx;
                const controlX2 = x2 - dx;

                return `M ${x1} ${y1} C ${controlX1} ${y1}, ${controlX2} ${y2}, ${x2} ${y2}`;
            },

            // ==================== NODE SELECTION ====================

            selectNode(nodeId) {
                // Deselect previous
                document.querySelectorAll('.flow-node.selected').forEach(n => n.classList.remove('selected'));

                this.selectedNode = nodeId;
                const nodeEl = document.getElementById('node-' + nodeId);
                nodeEl.classList.add('selected');

                const node = this.nodes.get(nodeId);
                this.showNodeProperties(node);
            },

            deselectNode() {
                document.querySelectorAll('.flow-node.selected').forEach(n => n.classList.remove('selected'));
                this.selectedNode = null;
                this.hideNodeProperties();
            },

            showNodeProperties(node) {
                document.getElementById('node-properties').style.display = 'block';
                document.getElementById('no-selection').style.display = 'none';

                document.getElementById('node-type-badge').textContent = node.type.toUpperCase();
                document.getElementById('node-label').value = node.label;

                // Show question-specific fields
                const isQuestion = node.type === 'question';
                document.getElementById('display-name-wrapper').style.display = isQuestion ? 'block' : 'none';
                document.getElementById('question-template-wrapper').style.display = isQuestion ? 'block' : 'none';
                document.getElementById('is-required-wrapper').style.display = isQuestion ? 'block' : 'none';
                document.getElementById('lead-status-wrapper').style.display = isQuestion ? 'block' : 'none';

                if (isQuestion && node.data) {
                    const qType = node.data.question_type;
                    const qId = node.data.question_id;
                    if (qType && qId) {
                        document.getElementById('node-display-name').value = `${qType}_${qId}`;
                    }
                    document.getElementById('node-question-template').value = node.data.question_template || '';
                    document.getElementById('node-is-required').value = node.isRequired ? '1' : '0';
                    document.getElementById('node-lead-status').value = node.data.lead_status_id || '';
                }
            },

            hideNodeProperties() {
                document.getElementById('node-properties').style.display = 'none';
                document.getElementById('no-selection').style.display = 'flex';
            },

            // ==================== PROPERTY UPDATES ====================

            updateNodeLabel(label) {
                if (!this.selectedNode) return;
                const node = this.nodes.get(this.selectedNode);
                node.label = label;
                document.querySelector(`#node-${this.selectedNode} .flow-node-title`).textContent = label;
            },

            updateNodeQuestion(select) {
                if (!this.selectedNode) return;
                const node = this.nodes.get(this.selectedNode);
                const opt = select.options[select.selectedIndex];

                if (opt && opt.value) {
                    node.data.question_type = opt.dataset.type;
                    node.data.question_id = opt.dataset.id;
                    node.data.field_name = opt.dataset.fieldName;
                    node.data.display_name = opt.dataset.displayName;
                    node.label = opt.dataset.displayName;

                    document.getElementById('node-label').value = node.label;
                    document.querySelector(`#node-${this.selectedNode} .flow-node-title`).textContent = node.label;
                }
            },

            updateNodeTemplate(template) {
                if (!this.selectedNode) return;
                const node = this.nodes.get(this.selectedNode);
                node.data.question_template = template;
            },

            updateNodeRequired(value) {
                if (!this.selectedNode) return;
                const node = this.nodes.get(this.selectedNode);
                node.isRequired = value === '1';
            },

            updateNodeLeadStatus(statusId) {
                if (!this.selectedNode) return;
                const node = this.nodes.get(this.selectedNode);
                node.data.lead_status_id = statusId || null;
            },

            // ==================== DELETE ====================

            deleteSelectedNode() {
                if (!this.selectedNode) return;

                // Remove connections
                this.connections = this.connections.filter(c =>
                    c.source !== this.selectedNode && c.target !== this.selectedNode
                );

                // Remove node element
                document.getElementById('node-' + this.selectedNode)?.remove();

                // Remove from state
                this.nodes.delete(this.selectedNode);
                this.selectedNode = null;

                this.renderConnections();
                this.hideNodeProperties();
                this.showToast('Node deleted!', 'success');
            },

            // ==================== CANVAS EVENTS ====================

            onCanvasMouseDown(e) {
                if (e.target === this.canvas || e.target === this.nodesContainer) {
                    this.deselectNode();
                }
            },

            onDrop(e) {
                e.preventDefault();
                const nodeType = e.dataTransfer.getData('node-type');
                if (!nodeType) return;

                const rect = this.canvas.getBoundingClientRect();
                const x = e.clientX - rect.left - 80; // Center offset
                const y = e.clientY - rect.top - 27;

                this.createNode(nodeType, x, y);
                this.showToast(`${nodeType} node added!`, 'success');
            },

            // ==================== ZOOM ====================

            zoomIn() {
                this.zoom = Math.min(2, this.zoom + 0.1);
                this.applyZoom();
            },

            zoomOut() {
                this.zoom = Math.max(0.5, this.zoom - 0.1);
                this.applyZoom();
            },

            resetZoom() {
                this.zoom = 1;
                this.applyZoom();
            },

            applyZoom() {
                this.nodesContainer.style.transform = `scale(${this.zoom})`;
                this.nodesContainer.style.transformOrigin = 'top left';
                this.svg.style.transform = `scale(${this.zoom})`;
                this.svg.style.transformOrigin = 'top left';
            },

            // ==================== SAVE/LOAD ====================

            async loadFlow() {
                try {
                    const response = await fetch('{{ route("admin.workflow.flowchart.data") }}');
                    const data = await response.json();

                    console.log('Loading flowchart data:', data);

                    // Create ID mapping (DB ID -> local ID)
                    const idMap = {};

                    // Create nodes
                    (data.nodes || []).forEach(node => {
                        const localId = this.createNode(node.type, node.position?.x || 100, node.position?.y || 100, {
                            dbId: node.id,
                            label: node.data?.label || node.type,
                            config: node.data?.config || {},
                            is_required: node.data?.isRequired
                        });
                        idMap[node.id] = localId;
                    });

                    // Create connections after nodes exist
                    setTimeout(() => {
                        (data.edges || []).forEach(edge => {
                            const sourceId = idMap[edge.source] || edge.source;
                            const targetId = idMap[edge.target] || edge.target;

                            if (this.nodes.has(sourceId) && this.nodes.has(targetId)) {
                                this.connections.push({
                                    id: 'conn_' + edge.id,
                                    source: sourceId,
                                    target: targetId,
                                    sourceHandle: edge.sourceHandle || 'output_1',
                                    targetHandle: edge.targetHandle || 'input_1'
                                });
                            }
                        });
                        this.renderConnections();
                    }, 100);

                } catch (error) {
                    console.error('Load error:', error);
                    this.showToast('Failed to load flowchart', 'error');
                }
            },

            async saveFlow() {
                const btn = document.getElementById('save-btn');
                btn.disabled = true;
                btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle></svg> Saving...';

                try {
                    const nodes = [];
                    const edges = [];

                    this.nodes.forEach((node, id) => {
                        nodes.push({
                            id: id,
                            type: node.type,
                            position: { x: node.x, y: node.y },
                            data: {
                                label: node.label,
                                config: node.data,
                                is_required: node.isRequired
                            }
                        });
                    });

                    this.connections.forEach(conn => {
                        edges.push({
                            source: conn.source,
                            target: conn.target,
                            sourceHandle: conn.sourceHandle,
                            targetHandle: conn.targetHandle
                        });
                    });

                    const response = await fetch('{{ route("admin.workflow.flowchart.save-all") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ nodes, edges })
                    });

                    const result = await response.json();
                    this.showToast(result.success ? 'Flow saved!' : result.error, result.success ? 'success' : 'error');

                } catch (error) {
                    this.showToast('Error: ' + error.message, 'error');
                }

                btn.disabled = false;
                btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Save Flow';
            },

            async clearFlow() {
                if (!confirm('Clear all nodes and connections?')) return;

                try {
                    await fetch('{{ route("admin.workflow.flowchart.clear") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    this.nodes.clear();
                    this.connections = [];
                    this.nodesContainer.innerHTML = '';
                    this.svg.innerHTML = '';
                    this.deselectNode();

                    this.showToast('Flow cleared!', 'success');
                } catch (error) {
                    this.showToast('Error: ' + error.message, 'error');
                }
            },

            // ==================== UTILITY ====================

            showToast(message, type) {
                const toast = document.createElement('div');
                toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-xl text-white font-medium z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
                toast.textContent = message;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 3000);
            }
        };

        // Initialize when DOM ready
        document.addEventListener('DOMContentLoaded', () => flowchart.init());
    </script>
@endpush