@extends('admin.layouts.app')

@section('title', 'Catalogue')
@section('page-title', 'Product Catalogue')

@section('content')
    <div class="space-y-6">
        <!-- Tabs -->
        <div class="glass rounded-xl p-2 inline-flex space-x-2">
            <a href="{{ route('admin.catalogue.index', ['tab' => 'fields']) }}"
                class="px-6 py-3 rounded-lg font-medium transition-all {{ $activeTab === 'fields' ? 'bg-primary-500 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    <span>Field Setup</span>
                </div>
            </a>
            <a href="{{ route('admin.catalogue.index', ['tab' => 'products']) }}"
                class="px-6 py-3 rounded-lg font-medium transition-all {{ $activeTab === 'products' ? 'bg-primary-500 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span>Products ({{ $products->total() }})</span>
                </div>
            </a>
        </div>

        @if($activeTab === 'fields')
            <!-- Field Setup Tab -->
            @include('admin.catalogue.partials.field-setup')
        @else
            <!-- Products Tab -->
            @include('admin.catalogue.partials.products')
        @endif
    </div>

    <!-- Add Field Modal -->
    <div id="addFieldModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 items-center justify-center"
        x-data="{ show: false }" x-show="show" x-cloak :class="show ? 'flex' : 'hidden'"
        @open-add-field.window="show = true" @close-modal.window="show = false" @keydown.escape.window="show = false">
        <div class="glass rounded-2xl p-6 w-full max-w-md mx-4" @click.away="show = false">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-white">Add New Field</h3>
                <button @click="show = false" class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('admin.catalogue-fields.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Field Name *</label>
                    <input type="text" name="field_name" required
                        class="w-full px-4 py-3 rounded-xl bg-dark-300 border border-gray-700 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                        placeholder="e.g., Model Number">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Field Type</label>
                    <select name="field_type" id="fieldTypeSelect"
                        class="w-full px-4 py-3 rounded-xl bg-dark-300 border border-gray-700 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                        onchange="toggleOptions(this.value)">
                        @foreach($fieldTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="optionsField" class="hidden">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Dropdown Options (comma separated)</label>
                    <input type="text" name="options"
                        class="w-full px-4 py-3 rounded-xl bg-dark-300 border border-gray-700 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                        placeholder="e.g., Gold, Silver, Black, Chrome">
                    <p class="text-xs text-gray-500 mt-1">Enter options separated by commas</p>
                </div>

                <div class="flex items-center space-x-6">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_unique" value="1"
                            class="w-5 h-5 rounded bg-dark-300 border-gray-700 text-primary-500 focus:ring-primary-500">
                        <span class="text-gray-300">Unique Values (no duplicates allowed)</span>
                    </label>
                </div>

                <div class="flex space-x-3 pt-4">
                    <button type="button" @click="show = false"
                        class="flex-1 px-4 py-3 rounded-xl bg-gray-700 text-white hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-3 rounded-xl bg-primary-500 text-white font-medium hover:bg-primary-600 transition-colors">
                        Add Field
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Field Modal -->
    <div id="editFieldModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 items-center justify-center"
        x-data="{ show: false, field: null }" x-show="show" x-cloak :class="show ? 'flex' : 'hidden'"
        @open-edit-field.window="show = true; field = $event.detail" @close-modal.window="show = false"
        @keydown.escape.window="show = false">
        <div class="glass rounded-2xl p-6 w-full max-w-md mx-4" @click.away="show = false">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-white">Edit Field</h3>
                <button @click="show = false" class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form x-bind:action="'/admin/catalogue-fields/' + (field ? field.id : '')" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Field Name *</label>
                    <input type="text" name="field_name" required x-model="field ? field.field_name : ''"
                        class="w-full px-4 py-3 rounded-xl bg-dark-300 border border-gray-700 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Field Type</label>
                    <select name="field_type" x-model="field ? field.field_type : 'text'"
                        class="w-full px-4 py-3 rounded-xl bg-dark-300 border border-gray-700 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                        @foreach($fieldTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="field && field.field_type === 'select'">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Dropdown Options</label>
                    <input type="text" name="options" x-model="field ? (field.options ? field.options.join(', ') : '') : ''"
                        class="w-full px-4 py-3 rounded-xl bg-dark-300 border border-gray-700 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                </div>

                <div class="flex items-center space-x-6">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_unique" value="1" x-bind:checked="field && field.is_unique"
                            class="w-5 h-5 rounded bg-dark-300 border-gray-700 text-primary-500 focus:ring-primary-500">
                        <span class="text-gray-300">Unique Values (no duplicates allowed)</span>
                    </label>
                </div>

                <div class="flex space-x-3 pt-4">
                    <button type="button" @click="show = false"
                        class="flex-1 px-4 py-3 rounded-xl bg-gray-700 text-white hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-3 rounded-xl bg-primary-500 text-white font-medium hover:bg-primary-600 transition-colors">
                        Update Field
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 items-center justify-center"
        x-data="{ show: false }" x-show="show" x-cloak :class="show ? 'flex' : 'hidden'"
        @open-add-product.window="show = true" @close-modal.window="show = false" @keydown.escape.window="show = false">
        <div class="glass rounded-2xl p-6 w-full max-w-lg mx-4 max-h-[80vh] overflow-y-auto" @click.away="show = false">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-white">Add New Product</h3>
                <button @click="show = false" class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('admin.catalogue.store') }}" method="POST" class="space-y-4">
                @csrf

                @foreach($fields as $field)
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ $field->field_name }}
                            @if($field->is_unique) <span class="text-xs text-yellow-400">(Unique)</span> @endif
                        </label>

                        @if($field->field_type === 'select' && $field->options)
                            <select name="data[{{ $field->field_key }}]"
                                class="w-full px-4 py-3 rounded-xl bg-dark-300 border border-gray-700 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                                <option value="">Select {{ $field->field_name }}</option>
                                @foreach($field->options as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        @elseif($field->field_type === 'number')
                            <input type="number" name="data[{{ $field->field_key }}]"
                                class="w-full px-4 py-3 rounded-xl bg-dark-300 border border-gray-700 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                placeholder="Enter {{ $field->field_name }}">
                        @else
                            <input type="text" name="data[{{ $field->field_key }}]"
                                class="w-full px-4 py-3 rounded-xl bg-dark-300 border border-gray-700 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                placeholder="Enter {{ $field->field_name }}">
                        @endif
                    </div>
                @endforeach

                <!-- Divider -->
                <div class="border-t border-gray-700 my-2"></div>

                <!-- Product Images -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        📷 Product Images
                    </label>
                    <input type="file" name="images[]" multiple accept="image/*"
                        class="w-full px-4 py-3 rounded-xl bg-dark-300 border border-gray-700 text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-500 file:text-white hover:file:bg-blue-600">
                    <p class="text-xs text-gray-500 mt-1">Select multiple images for this product</p>
                </div>

                <!-- Video URL -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        🎬 Video URL (Optional)
                    </label>
                    <input type="url" name="video_url"
                        class="w-full px-4 py-3 rounded-xl bg-dark-300 border border-gray-700 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                        placeholder="https://youtube.com/watch?v=... or direct video link">
                    <p class="text-xs text-gray-500 mt-1">YouTube, Vimeo or direct video link</p>
                </div>

                <div class="flex space-x-3 pt-4">
                    <button type="button" @click="show = false"
                        class="flex-1 px-4 py-3 rounded-xl bg-gray-700 text-white hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-3 rounded-xl bg-primary-500 text-white font-medium hover:bg-primary-600 transition-colors">
                        Add Product
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Import Modal -->
    <div id="importModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 items-center justify-center"
        x-data="{ show: false }" x-show="show" x-cloak :class="show ? 'flex' : 'hidden'" @open-import.window="show = true"
        @close-modal.window="show = false" @keydown.escape.window="show = false">
        <div class="glass rounded-2xl p-6 w-full max-w-md mx-4" @click.away="show = false">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-white">Import Products from Excel</h3>
                <button @click="show = false" class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mb-6">
                <a href="{{ route('admin.catalogue.import.sample') }}"
                    class="flex items-center justify-center space-x-2 w-full px-4 py-3 rounded-xl bg-green-600 text-white font-medium hover:bg-green-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Download Sample Template</span>
                </a>
                <p class="text-xs text-gray-400 mt-2 text-center">Download the sample Excel file with correct headers</p>
            </div>

            <form action="{{ route('admin.catalogue.import') }}" method="POST" enctype="multipart/form-data"
                class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Select Excel File</label>
                    <input type="file" name="file" accept=".xlsx,.xls" required
                        class="w-full px-4 py-3 rounded-xl bg-dark-300 border border-gray-700 text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-500 file:text-white hover:file:bg-primary-600">
                </div>

                <div class="flex space-x-3 pt-4">
                    <button type="button" @click="show = false"
                        class="flex-1 px-4 py-3 rounded-xl bg-gray-700 text-white hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-3 rounded-xl bg-primary-500 text-white font-medium hover:bg-primary-600 transition-colors">
                        Import Products
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function toggleOptions(value) {
                const optionsField = document.getElementById('optionsField');
                if (value === 'select') {
                    optionsField.classList.remove('hidden');
                } else {
                    optionsField.classList.add('hidden');
                }
            }
        </script>
    @endpush
@endsection