@extends('admin.layouts.app')

@section('title', 'Data Validation')

@section('content')
    <div class="p-4 lg:p-6" x-data="dataValidation()">
        <!-- Header -->
        <div class="glass rounded-2xl p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.workflow.index') }}"
                        class="p-2 rounded-xl bg-dark-300 hover:bg-dark-200 transition-colors">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div
                        class="w-14 h-14 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">Data Validation</h1>
                        <p class="text-gray-400 mt-1">View catalog data, check model codes, validate field mappings</p>
                    </div>
                </div>
                <button @click="runFullValidation()" class="btn-primary px-4 py-2 rounded-xl flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Run Validation
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="glass rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Total Products</p>
                        <p class="text-2xl font-bold text-white">{{ $catalogStats['total'] }}</p>
                    </div>
                </div>
            </div>

            <div class="glass rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Active</p>
                        <p class="text-2xl font-bold text-green-400">{{ $catalogStats['active'] }}</p>
                    </div>
                </div>
            </div>

            <div class="glass rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-red-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Inactive</p>
                        <p class="text-2xl font-bold text-red-400">{{ $catalogStats['inactive'] }}</p>
                    </div>
                </div>
            </div>

            <div class="glass rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Categories</p>
                        <p class="text-2xl font-bold text-purple-400">{{ $catalogStats['categories'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Tabs -->
        <div class="glass rounded-2xl overflow-hidden">
            <!-- Tab Navigation -->
            <div class="flex border-b border-gray-700">
                <button @click="activeTab = 'categories'"
                    :class="activeTab === 'categories' ? 'border-green-500 text-green-400' : 'border-transparent text-gray-400'"
                    class="px-6 py-4 font-medium border-b-2 transition-colors hover:text-white">
                    Categories & Models
                </button>
                <button @click="activeTab = 'mappings'"
                    :class="activeTab === 'mappings' ? 'border-green-500 text-green-400' : 'border-transparent text-gray-400'"
                    class="px-6 py-4 font-medium border-b-2 transition-colors hover:text-white">
                    Field Mappings
                </button>
                <button @click="activeTab = 'lookup'"
                    :class="activeTab === 'lookup' ? 'border-green-500 text-green-400' : 'border-transparent text-gray-400'"
                    class="px-6 py-4 font-medium border-b-2 transition-colors hover:text-white">
                    Model Lookup
                </button>
                <button @click="activeTab = 'issues'"
                    :class="activeTab === 'issues' ? 'border-green-500 text-green-400' : 'border-transparent text-gray-400'"
                    class="px-6 py-4 font-medium border-b-2 transition-colors hover:text-white">
                    <span class="flex items-center gap-2">
                        Issues
                        <span x-show="validationIssues.length > 0"
                            class="px-2 py-0.5 rounded-full bg-red-500/20 text-red-400 text-xs"
                            x-text="validationIssues.length"></span>
                    </span>
                </button>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Categories & Models Tab -->
                <div x-show="activeTab === 'categories'" x-cloak>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($modelsByCategory as $category => $models)
                            <div class="glass-light rounded-xl p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="font-semibold text-white">{{ $category }}</h3>
                                    <span class="px-2 py-1 rounded-lg bg-green-500/20 text-green-400 text-xs font-medium">
                                        {{ count($models) }} models
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($models as $model)
                                        <span
                                            class="px-2 py-1 rounded-lg bg-dark-300 text-gray-300 text-sm cursor-pointer hover:bg-primary-500/20 hover:text-primary-400 transition-colors"
                                            @click="lookupModel('{{ $model }}')">
                                            {{ $model }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        @if (empty($modelsByCategory))
                            <div class="col-span-full text-center py-8 text-gray-400">
                                No catalog data found. Import products first.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Field Mappings Tab -->
                <div x-show="activeTab === 'mappings'" x-cloak>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-700">
                                    <th class="text-left py-3 px-4 text-gray-400 font-medium">Catalogue Field</th>
                                    <th class="text-left py-3 px-4 text-gray-400 font-medium">Field Key</th>
                                    <th class="text-center py-3 px-4 text-gray-400 font-medium">Unique</th>
                                    <th class="text-left py-3 px-4 text-gray-400 font-medium">Mapped Question</th>
                                    <th class="text-center py-3 px-4 text-gray-400 font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fieldMappings as $mapping)
                                    <tr class="border-b border-gray-800 hover:bg-white/5">
                                        <td class="py-3 px-4 text-white">{{ $mapping['catalogue_field'] }}</td>
                                        <td class="py-3 px-4 text-gray-400 font-mono text-sm">{{ $mapping['catalogue_key'] }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if ($mapping['is_unique'])
                                                <span class="text-yellow-400">★</span>
                                            @else
                                                <span class="text-gray-600">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">
                                            @if ($mapping['question_field'])
                                                <span class="text-cyan-400">{{ $mapping['question_display'] }}</span>
                                            @else
                                                <span class="text-gray-500 italic">Not mapped</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if ($mapping['is_mapped'])
                                                <span
                                                    class="px-2 py-1 rounded-lg bg-green-500/20 text-green-400 text-xs">Linked</span>
                                            @else
                                                <span
                                                    class="px-2 py-1 rounded-lg bg-yellow-500/20 text-yellow-400 text-xs">Unlinked</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                                @if (empty($fieldMappings))
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-gray-400">
                                            No catalogue fields configured.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Model Lookup Tab -->
                <div x-show="activeTab === 'lookup'" x-cloak>
                    <div class="max-w-xl mx-auto">
                        <div class="flex gap-3 mb-6">
                            <input type="text" x-model="searchModel" placeholder="Enter model code to search..."
                                @keyup.enter="lookupModel(searchModel)"
                                class="flex-1 px-4 py-3 rounded-xl bg-dark-300 border border-gray-700 text-white placeholder-gray-500 focus:border-green-500 focus:ring-1 focus:ring-green-500">
                            <button @click="lookupModel(searchModel)"
                                class="px-6 py-3 rounded-xl bg-green-500 text-white font-medium hover:bg-green-600 transition-colors">
                                Search
                            </button>
                        </div>

                        <!-- Search Results -->
                        <div x-show="searchResults !== null" class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-white font-medium">Search Results</h3>
                                <span class="text-gray-400" x-text="searchResults?.length + ' product(s) found'"></span>
                            </div>

                            <template x-if="searchResults && searchResults.length > 0">
                                <div class="space-y-3">
                                    <template x-for="product in searchResults" :key="product.id">
                                        <div class="glass-light rounded-xl p-4">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-white font-medium"
                                                    x-text="product.data?.model_code || product.data?.model || 'N/A'"></span>
                                                <span :class="product.is_active ? 'bg-green-500/20 text-green-400' :
                                                            'bg-red-500/20 text-red-400'"
                                                    class="px-2 py-1 rounded-lg text-xs"
                                                    x-text="product.is_active ? 'Active' : 'Inactive'"></span>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2 text-sm">
                                                <template x-for="(value, key) in product.data" :key="key">
                                                    <div>
                                                        <span class="text-gray-500" x-text="key + ': '"></span>
                                                        <span class="text-gray-300" x-text="value || '-'"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="searchResults && searchResults.length === 0">
                                <div class="text-center py-8 text-gray-400">
                                    No products found for this model code.
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Issues Tab -->
                <div x-show="activeTab === 'issues'" x-cloak>
                    <div x-show="!validationRun" class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-lg font-medium text-white mb-2">Run Validation</h3>
                        <p class="text-gray-400 mb-4">Click the "Run Validation" button to check for issues in your
                            catalog.</p>
                        <button @click="runFullValidation()"
                            class="btn-primary px-6 py-3 rounded-xl font-medium inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Run Validation
                        </button>
                    </div>

                    <div x-show="validationRun && validationIssues.length === 0" class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto mb-4 text-green-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <h3 class="text-lg font-medium text-green-400 mb-2">All Good!</h3>
                        <p class="text-gray-400">No issues found in your catalog data.</p>
                    </div>

                    <div x-show="validationIssues.length > 0" class="space-y-4">
                        <template x-for="issue in validationIssues" :key="issue.product_id">
                            <div class="glass-light rounded-xl p-4 border border-red-500/30">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-white font-medium">Product ID: <span
                                            x-text="issue.product_id"></span></span>
                                    <span class="px-2 py-1 rounded-lg bg-red-500/20 text-red-400 text-xs"
                                        x-text="issue.issues.length + ' issue(s)'"></span>
                                </div>
                                <div class="space-y-1">
                                    <template x-for="(err, idx) in issue.issues" :key="idx">
                                        <p class="text-red-400 text-sm flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span x-text="err"></span>
                                        </p>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function dataValidation() {
            return {
                activeTab: 'categories',
                searchModel: '',
                searchResults: null,
                validationRun: false,
                validationIssues: [],

                async lookupModel(modelCode) {
                    if (!modelCode) return;

                    this.searchModel = modelCode;
                    this.activeTab = 'lookup';

                    try {
                        const response = await fetch(
                            `{{ route('admin.workflow.validation.lookup-model') }}?model_code=${encodeURIComponent(modelCode)}`);
                        const data = await response.json();
                        this.searchResults = data.products || [];
                    } catch (error) {
                        console.error('Lookup failed:', error);
                        this.searchResults = [];
                    }
                },

                async runFullValidation() {
                    try {
                        const response = await fetch(`{{ route('admin.workflow.validation.validate-all') }}`);
                        const data = await response.json();
                        this.validationRun = true;
                        this.validationIssues = data.issues || [];
                        this.activeTab = 'issues';
                    } catch (error) {
                        console.error('Validation failed:', error);
                    }
                }
            }
        }
    </script>
@endsection