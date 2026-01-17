<!-- Products Tab Content -->
<div class="space-y-6" x-data="{
    selectedIds: [],
    selectAll: false,
    
    toggleAll() {
        const checkboxes = document.querySelectorAll('.product-checkbox');
        if (this.selectAll) {
            this.selectedIds = Array.from(checkboxes).map(cb => parseInt(cb.value));
            checkboxes.forEach(cb => cb.checked = true);
        } else {
            this.selectedIds = [];
            checkboxes.forEach(cb => cb.checked = false);
        }
    },
    
    toggleOne(id, checked) {
        if (checked) {
            if (!this.selectedIds.includes(id)) {
                this.selectedIds.push(id);
            }
        } else {
            this.selectedIds = this.selectedIds.filter(i => i !== id);
        }
        const checkboxes = document.querySelectorAll('.product-checkbox');
        this.selectAll = this.selectedIds.length === checkboxes.length && checkboxes.length > 0;
    },
    
    deleteSelected() {
        if (this.selectedIds.length === 0) {
            alert('Please select products to delete');
            return;
        }
        if (!confirm('Delete ' + this.selectedIds.length + ' selected products?')) return;
        
        // Submit form
        document.getElementById('bulkDeleteIds').value = JSON.stringify(this.selectedIds);
        document.getElementById('bulkDeleteForm').submit();
    }
}">
    <!-- Hidden form for bulk delete -->
    <form id="bulkDeleteForm" action="{{ route('admin.catalogue.bulk-delete') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="ids" id="bulkDeleteIds" value="">
    </form>

    @if($fields->isEmpty())
        <!-- No Fields Warning -->
        <div class="glass rounded-2xl p-12 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h3 class="text-lg font-medium text-white mb-2">No Fields Configured</h3>
            <p class="text-gray-400 mb-6">Please create fields in the "Field Setup" tab before adding products.</p>
            <a href="{{ route('admin.catalogue.index', ['tab' => 'fields']) }}"
                class="btn-primary inline-block px-6 py-3 rounded-xl text-white font-medium">
                Go to Field Setup
            </a>
        </div>
    @else
        <!-- Header with Actions -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-white">Products</h2>
                <p class="text-gray-400 text-sm mt-1">{{ $products->total() }} products in catalogue</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button @click="$dispatch('open-import')"
                    class="px-4 py-2 rounded-xl bg-green-600 text-white font-medium hover:bg-green-700 transition-colors flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>Import Excel</span>
                </button>
                <button @click="$dispatch('open-add-product')"
                    class="btn-primary px-4 py-2 rounded-xl text-white font-medium flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span>Add Product</span>
                </button>
                @if($products->total() > 0)
                    <form action="{{ route('admin.catalogue.clear-all') }}" method="POST" class="inline"
                        onsubmit="return confirm('Delete ALL products? This cannot be undone!')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 rounded-xl bg-red-600/20 text-red-400 font-medium hover:bg-red-600/30 transition-colors flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span>Clear All</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Import Errors Display -->
        @if(!empty($importErrors))
            <div class="glass rounded-xl p-4 border border-red-500/50">
                <div class="flex items-center space-x-2 text-red-400 mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium">Import Errors ({{ count($importErrors) }} rows)</span>
                </div>
                <div class="max-h-40 overflow-y-auto space-y-2">
                    @foreach($importErrors as $error)
                        <div class="text-sm">
                            <span class="text-gray-400">Row {{ $error['row'] }}:</span>
                            <span class="text-red-400">{{ implode(', ', $error['errors']) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Search Bar - No icon -->
        <div class="glass rounded-xl p-3 sm:p-4">
            <form action="{{ route('admin.catalogue.index', ['tab' => 'products']) }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                <input type="hidden" name="tab" value="products">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by unique fields..."
                    class="flex-1 px-4 py-2.5 rounded-xl bg-dark-300 border border-gray-700 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                <div class="flex gap-2">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-500 text-white font-medium hover:bg-primary-600 transition-colors">
                        Search
                    </button>
                    @if(request('search'))
                        <a href="{{ route('admin.catalogue.index', ['tab' => 'products']) }}"
                            class="px-4 py-2.5 rounded-xl bg-gray-700 text-gray-300 hover:text-white hover:bg-gray-600 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Bulk Delete Button (shows when items selected) -->
        <div x-show="selectedIds.length > 0" x-cloak class="glass rounded-xl p-3 flex items-center justify-between">
            <span class="text-white"><span x-text="selectedIds.length"></span> products selected</span>
            <button @click="deleteSelected()"
                class="px-4 py-2 rounded-xl bg-red-600 text-white font-medium hover:bg-red-700 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete Selected
            </button>
        </div>

        @if($products->isEmpty())
            <!-- Empty State -->
            <div class="glass rounded-2xl p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <h3 class="text-lg font-medium text-white mb-2">No Products Found</h3>
                <p class="text-gray-400 mb-6">Try a different search or add new products.</p>
                <div class="flex items-center justify-center gap-4">
                    <button @click="$dispatch('open-add-product')"
                        class="btn-primary px-6 py-3 rounded-xl text-white font-medium">
                        Add Product
                    </button>
                    <button @click="$dispatch('open-import')"
                        class="px-6 py-3 rounded-xl bg-green-600 text-white font-medium hover:bg-green-700 transition-colors">
                        Import Excel
                    </button>
                </div>
            </div>
        @else
            <!-- Products Table -->
            <div class="glass rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-dark-300/50">
                            <tr>
                                <th class="px-4 py-4 text-left">
                                    <input type="checkbox"
                                        class="w-4 h-4 rounded bg-dark-300 border-gray-700 text-primary-500 focus:ring-primary-500"
                                        x-model="selectAll" @change="toggleAll()">
                                </th>
                                <th class="px-4 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">#</th>
                                @foreach($fields as $field)
                                    <th class="px-4 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                        {{ $field->field_name }}
                                        @if($field->is_unique)
                                            <span class="text-yellow-400">*</span>
                                        @endif
                                    </th>
                                @endforeach
                                @if(auth()->guard('admin')->user()->send_product_images)
                                    <th class="px-4 py-4 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Image</th>
                                @endif
                                <th class="px-4 py-4 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-4 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach($products as $index => $product)
                                <tr class="hover:bg-white/5 transition-colors">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <input type="checkbox"
                                            class="product-checkbox w-4 h-4 rounded bg-dark-300 border-gray-700 text-primary-500 focus:ring-primary-500"
                                            value="{{ $product->id }}"
                                            @change="toggleOne({{ $product->id }}, $event.target.checked)">
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-gray-500">
                                        {{ $products->firstItem() + $index }}
                                    </td>
                                    @foreach($fields as $field)
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="text-white">{{ $product->data[$field->field_key] ?? '-' }}</span>
                                        </td>
                                    @endforeach
                                    @if(auth()->guard('admin')->user()->send_product_images)
                                        <td class="px-4 py-4 whitespace-nowrap text-center">
                                            @if($product->image_url)
                                                <div class="flex items-center justify-center space-x-2">
                                                    <img src="{{ $product->image_url }}" alt="Product" class="w-10 h-10 rounded-lg object-cover">
                                                    <form action="{{ route('admin.catalogue.upload-image', $product) }}" method="POST" enctype="multipart/form-data" class="inline">
                                                        @csrf
                                                        <label class="cursor-pointer p-1 rounded bg-blue-500/20 text-blue-400 hover:bg-blue-500/30">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                            </svg>
                                                            <input type="file" name="image" accept="image/*" class="hidden" onchange="this.form.submit()">
                                                        </label>
                                                    </form>
                                                </div>
                                            @else
                                                <form action="{{ route('admin.catalogue.upload-image', $product) }}" method="POST" enctype="multipart/form-data" class="inline">
                                                    @csrf
                                                    <label class="cursor-pointer px-3 py-1 rounded-lg bg-primary-500/20 text-primary-400 hover:bg-primary-500/30 text-xs font-medium inline-flex items-center space-x-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                                        </svg>
                                                        <span>Upload</span>
                                                        <input type="file" name="image" accept="image/*" class="hidden" onchange="this.form.submit()">
                                                    </label>
                                                </form>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="px-4 py-4 whitespace-nowrap text-center">
                                        <span class="px-3 py-1 text-xs rounded-lg {{ $product->is_active ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400' }}">
                                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <form action="{{ route('admin.catalogue.toggle-status', $product) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="p-2 rounded-lg {{ $product->is_active ? 'bg-yellow-500/20 text-yellow-400 hover:bg-yellow-500/30' : 'bg-green-500/20 text-green-400 hover:bg-green-500/30' }} transition-colors"
                                                    title="{{ $product->is_active ? 'Deactivate' : 'Activate' }}">
                                                    @if($product->is_active)
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                        </svg>
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    @endif
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.catalogue.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Delete this product?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if($products->hasPages())
                <div class="mt-6">
                    {{ $products->withQueryString()->links() }}
                </div>
            @endif
        @endif
    @endif
</div>