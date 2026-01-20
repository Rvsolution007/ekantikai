@extends('admin.layouts.app')

@section('title', 'Flowchart Builder')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/jerosoler/Drawflow/dist/drawflow.min.css">
    <style>
        /* Canvas Background */
        #drawflow-container {
            width: 100%;
            height: calc(100vh - 200px);
            min-height: 600px;
            background: #1a1a2e;
            background-image:
                linear-gradient(rgba(99, 102, 241, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99, 102, 241, 0.03) 1px, transparent 1px);
            background-size: 25px 25px;
            position: relative;
            overflow: hidden;
        }

        /* N8N Style Nodes */
        .drawflow .drawflow-node {
            background: #262640;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            min-width: 160px;
            max-width: 200px;
            padding: 0;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
            cursor: move;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            overflow: visible !important;
            z-index: 10;
        }

        .drawflow .drawflow-node:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(99, 102, 241, 0.3);
        }

        .drawflow .drawflow-node.selected {
            box-shadow: 0 0 0 2px #6366f1, 0 8px 24px rgba(99, 102, 241, 0.4);
        }

        /* Connection Handles */
        .drawflow .drawflow-node .inputs,
        .drawflow .drawflow-node .outputs {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
        }

        .drawflow .drawflow-node .inputs {
            left: -12px;
        }

        .drawflow .drawflow-node .outputs {
            right: -12px;
        }

        .drawflow .drawflow-node .input,
        .drawflow .drawflow-node .output {
            width: 20px;
            height: 20px;
            background: #1a1a2e;
            border: 3px solid #6366f1;
            border-radius: 50%;
            cursor: crosshair;
        }

        .drawflow .drawflow-node .input:hover,
        .drawflow .drawflow-node .output:hover {
            background: #6366f1;
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.9);
        }

        /* Connection Lines */
        .drawflow .connection .main-path {
            stroke: #6366f1;
            stroke-width: 3;
            fill: none;
            cursor: pointer;
            transition: stroke 0.2s ease;
        }

        .drawflow .connection:hover .main-path {
            stroke: #a855f7;
            stroke-width: 4;
        }

        /* Connection Delete Button - visible X in middle */
        .connection-delete-btn {
            position: absolute;
            width: 24px;
            height: 24px;
            background: #ef4444;
            border: 2px solid #fff;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            color: #fff;
            z-index: 9999;
            opacity: 0;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            transform: translate(-50%, -50%);
            pointer-events: auto;
        }

        .connection-delete-btn:hover {
            background: #dc2626;
            transform: translate(-50%, -50%) scale(1.2);
        }

        .drawflow .connection:hover .connection-delete-btn {
            opacity: 1;
        }

        /* Hide default node delete */
        .drawflow-delete {
            display: none !important;
        }

        /* N8N Node Card */
        .n8n-node {
            display: flex;
            align-items: stretch;
            min-height: 50px;
            pointer-events: none;
        }

        .n8n-node-icon {
            width: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 7px 0 0 7px;
            font-size: 18px;
        }

        .n8n-node-content {
            flex: 1;
            padding: 10px 12px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .n8n-node-title {
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            margin: 0;
            line-height: 1.3;
        }

        .n8n-node-subtitle {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 2px;
        }

        /* Node Type Colors */
        .n8n-start .n8n-node-icon {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .n8n-question .n8n-node-icon {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
        }

        .n8n-condition .n8n-node-icon {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .n8n-action .n8n-node-icon {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }

        .n8n-end .n8n-node-icon {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        /* Sidebar Nodes */
        .node-palette-item {
            cursor: grab;
            transition: all 0.2s ease;
            user-select: none;
        }

        .node-palette-item:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
        }

        .drawflow-delete {
            display: none !important;
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
                    <button onclick="flowchartEditor.clearFlow()"
                        class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 text-sm flex items-center gap-2 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Clear
                    </button>
                    <button onclick="flowchartEditor.saveFlow()" id="save-btn"
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
                    <div class="node-palette-item glass-light rounded-xl p-3" draggable="true"
                        ondragstart="flowchartEditor.drag(event)" data-node="start">
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

                    <div class="node-palette-item glass-light rounded-xl p-3" draggable="true"
                        ondragstart="flowchartEditor.drag(event)" data-node="question">
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

                    <div class="node-palette-item glass-light rounded-xl p-3" draggable="true"
                        ondragstart="flowchartEditor.drag(event)" data-node="condition">
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

                    <div class="node-palette-item glass-light rounded-xl p-3" draggable="true"
                        ondragstart="flowchartEditor.drag(event)" data-node="action">
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

                    <div class="node-palette-item glass-light rounded-xl p-3" draggable="true"
                        ondragstart="flowchartEditor.drag(event)" data-node="end">
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
                        <li>• Connect outputs to inputs</li>
                        <li>• Click node to select</li>
                        <li>• Delete key to remove</li>
                    </ul>
                </div>
            </div>

            <!-- Canvas Area -->
            <div class="flex-1 glass rounded-2xl relative overflow-hidden">
                <div id="drawflow-container" ondrop="flowchartEditor.drop(event)" ondragover="event.preventDefault()"></div>
            </div>

            <!-- Right Sidebar -->
            <div class="w-80 glass rounded-2xl p-4 flex-shrink-0">
                <div id="node-properties" style="display: none;">
                    <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Node Properties</h3>
                    <div class="mb-4">
                        <span id="node-type-badge" class="px-3 py-1 rounded-full text-xs font-medium"></span>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm text-gray-400 mb-2">Label</label>
                        <input type="text" id="node-label"
                            class="w-full px-3 py-2 rounded-lg text-white text-sm bg-dark-300 border border-white/10">
                    </div>

                    <!-- Display Name Dropdown - Only for Question nodes -->
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
                        <p class="text-xs text-gray-500 mt-1">Link this node to an existing question</p>
                    </div>

                    <!-- Ask Question Template - Custom question format for bot -->
                    <div class="mb-4" id="question-template-wrapper" style="display: none;">
                        <label class="block text-sm text-gray-400 mb-2">📝 Ask Question Format</label>
                        <textarea id="node-question-template" rows="3"
                            class="w-full px-3 py-2 rounded-lg text-white text-sm bg-dark-300 border border-white/10 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                            placeholder="e.g., XYZ product with YZ model confirm, ab konsa Size chahiye?"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Bot will ask in this format. Write in any language (Hindi,
                            Hinglish, English)</p>
                    </div>

                    <!-- Required/Optional Dropdown - Only for Question nodes -->
                    <div class="mb-4" id="is-required-wrapper" style="display: none;">
                        <label class="block text-sm text-gray-400 mb-2">Field Type</label>
                        <select id="node-is-required"
                            class="w-full px-3 py-2 rounded-lg text-white text-sm bg-dark-300 border border-white/10 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            <option value="1">✅ Required - Must be answered</option>
                            <option value="0">⏭️ Optional - Can be skipped</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Required fields must be answered before proceeding</p>
                    </div>

                    <!-- Ask Digit - Only visible for Optional questions -->
                    <div class="mb-4" id="ask-digit-wrapper" style="display: none;">
                        <label class="block text-sm text-gray-400 mb-2">Ask Digit (How many times to ask)</label>
                        <input type="number" id="node-ask-digit" min="1" max="10" value="1"
                            class="w-full px-3 py-2 rounded-lg text-white text-sm bg-dark-300 border border-white/10">
                        <p class="text-xs text-gray-500 mt-1">Bot will ask this optional question this many times during
                            conversation (spread across different messages)</p>
                    </div>

                    <!-- Lead Status Connection - For connecting question completion to lead status -->
                    <div class="mb-4" id="lead-status-wrapper" style="display: none;">
                        <label class="block text-sm text-gray-400 mb-2">🎯 Lead Status Connection</label>
                        <select id="node-lead-status"
                            class="w-full px-3 py-2 rounded-lg text-white text-sm bg-dark-300 border border-white/10 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            <option value="">-- No status change --</option>
                            @foreach($leadStatuses ?? [] as $status)
                                <option value="{{ $status->id }}" style="color: {{ $status->color }}">
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">When this question is answered, move lead to this status</p>
                    </div>

                    <button onclick="flowchartEditor.deleteNode()"
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
    <script src="https://cdn.jsdelivr.net/gh/jerosoler/Drawflow/dist/drawflow.min.js"></script>
    <script>
        // Global flowchart editor
        const flowchartEditor = {
            editor: null,
            selectedNode: null,
            nodeCounter: 1,

            nodeTemplates: {
                start: `<div class="n8n-node n8n-start"><div class="n8n-node-icon">▶</div><div class="n8n-node-content"><div class="n8n-node-title">Start</div><div class="n8n-node-subtitle">Entry point</div></div></div>`,
                question: `<div class="n8n-node n8n-question"><div class="n8n-node-icon">❓</div><div class="n8n-node-content"><div class="n8n-node-title">Question</div><div class="n8n-node-subtitle">Ask user</div></div></div>`,
                condition: `<div class="n8n-node n8n-condition"><div class="n8n-node-icon">⚡</div><div class="n8n-node-content"><div class="n8n-node-title">Condition</div><div class="n8n-node-subtitle">Branch logic</div></div></div>`,
                action: `<div class="n8n-node n8n-action"><div class="n8n-node-icon">⚙</div><div class="n8n-node-content"><div class="n8n-node-title">Action</div><div class="n8n-node-subtitle">Execute task</div></div></div>`,
                end: `<div class="n8n-node n8n-end"><div class="n8n-node-icon">🏁</div><div class="n8n-node-content"><div class="n8n-node-title">End</div><div class="n8n-node-subtitle">Complete flow</div></div></div>`
            },

            nodeConfigs: {
                start: { inputs: 0, outputs: 1 },
                question: { inputs: 1, outputs: 1 },
                condition: { inputs: 1, outputs: 2 },
                action: { inputs: 1, outputs: 1 },
                end: { inputs: 1, outputs: 0 }
            },

            init() {
                const container = document.getElementById('drawflow-container');
                this.editor = new Drawflow(container);
                this.editor.reroute = false;
                this.editor.curvature = 0.5;
                this.editor.start();

                // Events
                this.editor.on('nodeSelected', (id) => this.selectNode(id));
                this.editor.on('nodeUnselected', () => this.deselectNode());

                // Add delete button when connection created
                this.editor.on('connectionCreated', (info) => {
                    console.log('Connection created:', info);
                    setTimeout(() => this.addDeleteButtons(), 100);
                });

                // Label input change
                document.getElementById('node-label').addEventListener('input', (e) => {
                    if (this.selectedNode) {
                        const node = document.querySelector(`#node-${this.selectedNode} .n8n-node-title`);
                        if (node) node.textContent = e.target.value;
                        this.editor.updateNodeDataFromId(this.selectedNode, { label: e.target.value });
                    }
                });

                // Display Name dropdown change (for Question nodes)
                document.getElementById('node-display-name').addEventListener('change', (e) => {
                    if (this.selectedNode) {
                        const select = e.target;
                        const selectedOption = select.options[select.selectedIndex];

                        if (selectedOption && selectedOption.value) {
                            const questionType = selectedOption.dataset.type; // 'product' or 'global'
                            const questionId = selectedOption.dataset.id;
                            const fieldName = selectedOption.dataset.fieldName;
                            const displayName = selectedOption.dataset.displayName;

                            // Update node data with question config
                            const nodeInfo = this.editor.getNodeFromId(this.selectedNode);
                            const currentData = nodeInfo.data || {};

                            this.editor.updateNodeDataFromId(this.selectedNode, {
                                ...currentData,
                                label: displayName,
                                config: {
                                    ...currentData.config,
                                    question_type: questionType,
                                    question_id: questionId,
                                    field_name: fieldName,
                                    display_name: displayName
                                }
                            });

                            // Update node visual
                            const titleEl = document.querySelector(`#node-${this.selectedNode} .n8n-node-title`);
                            const subtitleEl = document.querySelector(`#node-${this.selectedNode} .n8n-node-subtitle`);
                            if (titleEl) titleEl.textContent = displayName;
                            if (subtitleEl) subtitleEl.textContent = questionType === 'product' ? '📦 Product' : '🌐 Global';

                            // Also update the label input
                            document.getElementById('node-label').value = displayName;

                            // Show/hide fields based on question type
                            const uniqueFieldWrapper = document.getElementById('unique-field-wrapper');
                            const isRequiredWrapper = document.getElementById('is-required-wrapper');
                            const askDigitWrapper = document.getElementById('ask-digit-wrapper');

                            if (questionType === 'product') {
                                // Product Questions: Show Unique Field, Hide Required
                                uniqueFieldWrapper.style.display = 'block';
                                isRequiredWrapper.style.display = 'none';
                                askDigitWrapper.style.display = 'none';
                            } else {
                                // Global Questions: Show Required, Hide Unique Field
                                uniqueFieldWrapper.style.display = 'none';
                                isRequiredWrapper.style.display = 'block';
                                // Ask Digit shown based on Required selection
                            }
                        }
                    }
                });

                // Is Required dropdown change (for Question nodes)
                document.getElementById('node-is-required').addEventListener('change', (e) => {
                    if (this.selectedNode) {
                        const isRequired = e.target.value === '1';
                        const nodeInfo = this.editor.getNodeFromId(this.selectedNode);
                        const currentData = nodeInfo.data || {};

                        this.editor.updateNodeDataFromId(this.selectedNode, {
                            ...currentData,
                            is_required: isRequired
                        });

                        // Show/hide Ask Digit field based on required/optional
                        const askDigitWrapper = document.getElementById('ask-digit-wrapper');
                        if (!isRequired) {
                            askDigitWrapper.style.display = 'block';
                        } else {
                            askDigitWrapper.style.display = 'none';
                        }

                        console.log('Node is_required updated:', isRequired);
                    }
                });

                // Ask Digit input change
                document.getElementById('node-ask-digit').addEventListener('input', (e) => {
                    if (this.selectedNode) {
                        const askDigit = parseInt(e.target.value) || 1;
                        const nodeInfo = this.editor.getNodeFromId(this.selectedNode);
                        const currentData = nodeInfo.data || {};

                        this.editor.updateNodeDataFromId(this.selectedNode, {
                            ...currentData,
                            config: {
                                ...currentData.config,
                                ask_digit: askDigit
                            }
                        });
                        console.log('Ask digit updated:', askDigit);
                    }
                });

                // Question Template textarea change
                document.getElementById('node-question-template').addEventListener('input', (e) => {
                    if (this.selectedNode) {
                        const questionTemplate = e.target.value;
                        const nodeInfo = this.editor.getNodeFromId(this.selectedNode);
                        const currentData = nodeInfo.data || {};

                        this.editor.updateNodeDataFromId(this.selectedNode, {
                            ...currentData,
                            config: {
                                ...currentData.config,
                                question_template: questionTemplate
                            }
                        });
                        console.log('Question template updated:', questionTemplate);
                    }
                });

                // Lead Status dropdown change
                document.getElementById('node-lead-status').addEventListener('change', (e) => {
                    if (this.selectedNode) {
                        const leadStatusId = e.target.value || null;
                        const nodeInfo = this.editor.getNodeFromId(this.selectedNode);
                        const currentData = nodeInfo.data || {};

                        this.editor.updateNodeDataFromId(this.selectedNode, {
                            ...currentData,
                            config: {
                                ...currentData.config,
                                lead_status_id: leadStatusId
                            }
                        });
                        console.log('Lead status updated:', leadStatusId);
                    }
                });

                // Load saved flow
                this.loadFlow();

                console.log('Flowchart Editor initialized!');
            },

            // Add X delete buttons to all connections
            addDeleteButtons() {
                document.querySelectorAll('.connection').forEach(conn => {
                    // Skip if already has delete button
                    if (conn.querySelector('.connection-delete-btn')) return;

                    const path = conn.querySelector('.main-path');
                    if (!path) return;

                    // Get path midpoint
                    const pathLength = path.getTotalLength();
                    const midPoint = path.getPointAtLength(pathLength / 2);

                    // Create delete button
                    const btn = document.createElement('div');
                    btn.className = 'connection-delete-btn';
                    btn.innerHTML = '✕';
                    btn.style.left = midPoint.x + 'px';
                    btn.style.top = midPoint.y + 'px';

                    // Extract connection info from class names
                    let outputNode, inputNode, outputClass, inputClass;
                    conn.classList.forEach(cls => {
                        if (cls.startsWith('node_out_node-')) outputNode = cls.replace('node_out_node-', '');
                        if (cls.startsWith('node_in_node-')) inputNode = cls.replace('node_in_node-', '');
                        if (cls.startsWith('output_')) outputClass = cls.replace('output_', '');
                        if (cls.startsWith('input_')) inputClass = cls.replace('input_', '');
                    });

                    btn.onclick = (e) => {
                        e.stopPropagation();
                        if (confirm('Delete this connection?')) {
                            this.editor.removeSingleConnection(outputNode, inputNode, 'output_' + outputClass, 'input_' + inputClass);
                            this.showToast('Connection deleted', 'success');
                        }
                    };

                    conn.appendChild(btn);
                });
            },

            async loadFlow() {
                try {
                    const response = await fetch('{{ route("admin.workflow.flowchart.data") }}');
                    const data = await response.json();

                    console.log('Loaded data:', data);

                    if (data.nodes && data.nodes.length > 0) {
                        // Map database IDs to new drawflow IDs
                        const idMap = {};

                        // Add nodes with full data
                        data.nodes.forEach(node => {
                            // Prepare full node data for addNode
                            const nodeData = {
                                label: node.data?.label || node.type,
                                dbId: node.id,
                                config: node.data?.config || {},
                                is_required: node.data?.isRequired !== undefined ? node.data.isRequired : true,
                            };

                            // Merge config with top-level fields for backward compatibility
                            if (node.data?.config) {
                                nodeData.config = {
                                    ...node.data.config,
                                    ask_digit: node.data.askDigit || node.data.config?.ask_digit || 1,
                                    is_unique_field: node.data.isUniqueField || node.data.config?.is_unique_field || false,
                                    lead_status_id: node.data.config?.lead_status_id || null,
                                };
                            }

                            const newId = this.addNode(
                                node.type,
                                node.position?.x || 100,
                                node.position?.y || 100,
                                nodeData
                            );
                            idMap[node.id] = newId;
                            console.log(`Node ${node.id} -> Drawflow ${newId}`, nodeData);
                        });

                        console.log('ID Map:', idMap);
                        console.log('Edges to create:', data.edges);

                        // Add connections after small delay
                        setTimeout(() => {
                            (data.edges || []).forEach(edge => {
                                const sourceId = idMap[edge.source];
                                const targetId = idMap[edge.target];
                                console.log(`Edge: DB ${edge.source}->${edge.target} | Drawflow ${sourceId}->${targetId}`);
                                if (sourceId && targetId) {
                                    try {
                                        this.editor.addConnection(sourceId, targetId, 'output_1', 'input_1');
                                        console.log('Connection added successfully');
                                    } catch (e) {
                                        console.log('Connection error:', e);
                                    }
                                } else {
                                    console.log('Missing ID mapping for edge');
                                }
                            });
                            this.addDeleteButtons();
                        }, 300);
                    }
                } catch (error) {
                    console.error('Load error:', error);
                }
            },

            drag(event) {
                event.dataTransfer.setData('node', event.target.closest('[data-node]').dataset.node);
            },

            drop(event) {
                event.preventDefault();
                const nodeType = event.dataTransfer.getData('node');
                if (!nodeType) return;

                const rect = document.getElementById('drawflow-container').getBoundingClientRect();
                const pos_x = event.clientX - rect.left;
                const pos_y = event.clientY - rect.top;

                this.addNode(nodeType, pos_x, pos_y);
            },

            addNode(type, pos_x, pos_y, data = {}) {
                const config = this.nodeConfigs[type];
                const template = this.nodeTemplates[type];
                const label = data.label || `${type.charAt(0).toUpperCase() + type.slice(1)} ${this.nodeCounter++}`;

                const nodeId = this.editor.addNode(type, config.inputs, config.outputs, pos_x, pos_y, type, { label, ...data }, template);

                // Update title
                setTimeout(() => {
                    const node = document.querySelector(`#node-${nodeId} .n8n-node-title`);
                    if (node) node.textContent = label;
                }, 10);

                return nodeId;
            },

            selectNode(id) {
                this.selectedNode = id;
                const nodeInfo = this.editor.getNodeFromId(id);

                document.getElementById('node-properties').style.display = 'block';
                document.getElementById('no-selection').style.display = 'none';
                document.getElementById('node-label').value = nodeInfo.data.label || nodeInfo.name;

                const badge = document.getElementById('node-type-badge');
                badge.textContent = nodeInfo.name.toUpperCase();
                badge.className = `px-3 py-1 rounded-full text-xs font-medium bg-primary-500/20 text-primary-400`;

                // Get all wrappers
                const displayNameWrapper = document.getElementById('display-name-wrapper');
                const displayNameSelect = document.getElementById('node-display-name');
                const isRequiredWrapper = document.getElementById('is-required-wrapper');
                const isRequiredSelect = document.getElementById('node-is-required');
                const askDigitWrapper = document.getElementById('ask-digit-wrapper');
                const askDigitInput = document.getElementById('node-ask-digit');
                const uniqueFieldWrapper = document.getElementById('unique-field-wrapper');
                const uniqueFieldCheck = document.getElementById('node-unique-field');
                const leadStatusWrapper = document.getElementById('lead-status-wrapper');
                const leadStatusSelect = document.getElementById('node-lead-status');
                const questionTemplateWrapper = document.getElementById('question-template-wrapper');
                const questionTemplateInput = document.getElementById('node-question-template');

                if (nodeInfo.name === 'question') {
                    displayNameWrapper.style.display = 'block';
                    leadStatusWrapper.style.display = 'block';
                    questionTemplateWrapper.style.display = 'block';

                    // Restore saved question selection if exists
                    const config = nodeInfo.data?.config || {};

                    // Populate question template
                    questionTemplateInput.value = config.question_template || '';

                    if (config.question_type && config.question_id) {
                        const savedValue = `${config.question_type}_${config.question_id}`;
                        displayNameSelect.value = savedValue;

                        // Product Questions: Show Unique Field, Hide Required
                        // Global Questions: Show Required/Optional, Hide Unique Field
                        if (config.question_type === 'product') {
                            // Product Questions - show Unique Field, hide Required
                            uniqueFieldWrapper.style.display = 'block';
                            uniqueFieldCheck.checked = config.is_unique_field || false;
                            isRequiredWrapper.style.display = 'none';
                            askDigitWrapper.style.display = 'none';
                        } else {
                            // Global Questions - show Required/Optional with Ask Digit
                            uniqueFieldWrapper.style.display = 'none';
                            isRequiredWrapper.style.display = 'block';

                            // Restore is_required value (default to true/required)
                            const isRequired = nodeInfo.data?.is_required !== undefined ? nodeInfo.data.is_required : true;
                            isRequiredSelect.value = isRequired ? '1' : '0';

                            // Show Ask Digit only for optional global questions
                            if (!isRequired) {
                                askDigitWrapper.style.display = 'block';
                                askDigitInput.value = config.ask_digit || 1;
                            } else {
                                askDigitWrapper.style.display = 'none';
                            }
                        }
                    } else {
                        displayNameSelect.value = '';
                        uniqueFieldWrapper.style.display = 'none';
                        isRequiredWrapper.style.display = 'none';
                        askDigitWrapper.style.display = 'none';
                    }

                    // Restore lead status
                    leadStatusSelect.value = config.lead_status_id || '';
                } else {
                    displayNameWrapper.style.display = 'none';
                    isRequiredWrapper.style.display = 'none';
                    askDigitWrapper.style.display = 'none';
                    uniqueFieldWrapper.style.display = 'none';
                    leadStatusWrapper.style.display = 'none';
                    questionTemplateWrapper.style.display = 'none';
                    displayNameSelect.value = '';
                }
            },

            deselectNode() {
                this.selectedNode = null;
                document.getElementById('node-properties').style.display = 'none';
                document.getElementById('no-selection').style.display = 'flex';
                document.getElementById('display-name-wrapper').style.display = 'none';
            },

            deleteNode() {
                if (this.selectedNode) {
                    this.editor.removeNodeId('node-' + this.selectedNode);
                    this.deselectNode();
                }
            },

            clearFlow() {
                if (confirm('Clear all nodes?')) {
                    this.editor.clear();
                    this.deselectNode();
                    this.showToast('Flow cleared!', 'success');
                }
            },

            async saveFlow() {
                const btn = document.getElementById('save-btn');
                btn.disabled = true;
                btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle></svg> Saving...';

                try {
                    const exportData = this.editor.export();
                    const moduleData = exportData.drawflow?.Home?.data || {};

                    const nodes = [];
                    const edges = [];

                    // Use drawflow internal IDs consistently
                    Object.entries(moduleData).forEach(([id, node]) => {
                        nodes.push({
                            id: id, // Use drawflow internal ID
                            type: node.name,
                            position: { x: node.pos_x, y: node.pos_y },
                            data: {
                                label: node.data?.label || node.name,
                                config: node.data?.config || {},
                                is_required: node.data?.is_required !== undefined ? node.data.is_required : true
                            }
                        });

                        Object.entries(node.outputs || {}).forEach(([outputKey, output]) => {
                            output.connections?.forEach(conn => {
                                edges.push({
                                    source: id, // Drawflow ID
                                    target: conn.node, // Drawflow ID
                                    sourceHandle: outputKey,
                                    targetHandle: conn.input
                                });
                            });
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

            showToast(message, type) {
                const toast = document.createElement('div');
                toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-xl text-white font-medium z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
                toast.textContent = message;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 3000);
            }
        };

        // Initialize when DOM ready
        document.addEventListener('DOMContentLoaded', () => {
            flowchartEditor.init();
        });
    </script>
@endpush