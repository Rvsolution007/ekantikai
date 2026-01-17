@extends('superadmin.layouts.app')

@section('title', 'AI System Prompt Preview')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-white">AI System Prompt Preview</h1>
                <p class="text-gray-400">View the actual prompt sent to AI including catalogue data</p>
            </div>
            <a href="{{ route('superadmin.ai-config.index') }}"
                class="px-4 py-2 bg-gray-500/20 text-gray-400 rounded-xl hover:bg-gray-500/30 transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Config
            </a>
        </div>

        <!-- Admin Selector -->
        <div class="glass rounded-2xl p-6">
            <div class="flex items-center gap-4">
                <label class="text-white font-medium">Select Admin:</label>
                <select id="adminSelect" class="input-dark px-4 py-2 rounded-xl text-white">
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}">{{ $admin->company_name ?? $admin->name }} (ID: {{ $admin->id }})
                        </option>
                    @endforeach
                </select>
                <button onclick="loadPrompt()" id="loadBtn"
                    class="px-6 py-2 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-xl hover:from-blue-600 hover:to-purple-600 transition-all font-medium">
                    Load Prompt
                </button>
            </div>
        </div>

        <!-- Catalogue Info Card -->
        <div id="catalogueInfo" class="glass rounded-2xl p-6 hidden">
            <h3 class="text-lg font-semibold text-white mb-4">📦 Catalogue Data for This Admin</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-gray-700/30 rounded-xl p-4 text-center">
                    <div class="text-3xl font-bold text-blue-400" id="totalProducts">0</div>
                    <div class="text-gray-400 text-sm">Total Products</div>
                </div>
                <div class="bg-gray-700/30 rounded-xl p-4 text-center">
                    <div class="text-3xl font-bold text-green-400" id="productTypes">0</div>
                    <div class="text-gray-400 text-sm">Product Types</div>
                </div>
                <div class="bg-gray-700/30 rounded-xl p-4 text-center">
                    <div class="text-3xl font-bold text-purple-400" id="categories">0</div>
                    <div class="text-gray-400 text-sm">Categories</div>
                </div>
                <div class="bg-gray-700/30 rounded-xl p-4 text-center">
                    <div class="text-3xl font-bold text-yellow-400" id="fieldRules">0</div>
                    <div class="text-gray-400 text-sm">Field Rules</div>
                </div>
            </div>

            <div id="productTypesList" class="mt-4 hidden">
                <h4 class="text-white font-medium mb-2">Product Types:</h4>
                <div class="flex flex-wrap gap-2" id="productTypesContainer"></div>
            </div>
        </div>

        <!-- System Prompt Display -->
        <div class="glass rounded-2xl p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-white">📝 Complete System Prompt</h3>
                <button onclick="copyPrompt()"
                    class="px-4 py-2 bg-gray-500/20 text-gray-400 rounded-lg hover:bg-gray-500/30 transition-colors text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                    </svg>
                    Copy
                </button>
            </div>

            <div id="promptLoading" class="text-center py-8 text-gray-500">
                <p>Select an admin and click "Load Prompt" to view the system prompt</p>
            </div>

            <pre id="promptContent"
                class="hidden bg-gray-800/50 rounded-xl p-4 text-gray-300 text-sm overflow-x-auto whitespace-pre-wrap max-h-[600px] overflow-y-auto font-mono"></pre>
        </div>

        <!-- Note -->
        <div class="glass rounded-2xl p-6 bg-blue-500/10 border border-blue-500/20">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h4 class="text-blue-400 font-medium">How It Works</h4>
                    <p class="text-gray-400 text-sm mt-1">
                        This prompt is sent to AI with every message. When you update the <strong>Catalogue</strong> or
                        <strong>Workflow</strong>,
                        the AI automatically uses the new data in subsequent conversations. The catalogue data is fetched
                        fresh from the database for each message.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function loadPrompt() {
            const adminId = document.getElementById('adminSelect').value;
            const loadBtn = document.getElementById('loadBtn');
            const promptContent = document.getElementById('promptContent');
            const promptLoading = document.getElementById('promptLoading');
            const catalogueInfo = document.getElementById('catalogueInfo');

            loadBtn.disabled = true;
            loadBtn.textContent = 'Loading...';

            try {
                const response = await fetch('{{ route("superadmin.ai-config.prompt-preview.get") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ admin_id: adminId })
                });

                const data = await response.json();

                if (data.success) {
                    // Show prompt
                    promptContent.textContent = data.prompt;
                    promptContent.classList.remove('hidden');
                    promptLoading.classList.add('hidden');

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
                            `<span class="px-3 py-1 bg-blue-500/20 text-blue-300 rounded-full text-sm">${t}</span>`
                        ).join('');
                    }
                } else {
                    promptLoading.textContent = 'Error: ' + (data.error || 'Failed to load prompt');
                    promptLoading.classList.remove('hidden');
                    promptContent.classList.add('hidden');
                }
            } catch (error) {
                promptLoading.textContent = 'Error: ' + error.message;
            }

            loadBtn.disabled = false;
            loadBtn.textContent = 'Load Prompt';
        }

        function copyPrompt() {
            const prompt = document.getElementById('promptContent').textContent;
            navigator.clipboard.writeText(prompt).then(() => {
                alert('Prompt copied to clipboard!');
            });
        }

        // Auto-load first admin on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadPrompt();
        });
    </script>
@endsection