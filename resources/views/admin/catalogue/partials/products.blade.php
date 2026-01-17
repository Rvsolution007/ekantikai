<!-- Products Tab Content -->
<div class="space-y-6" x-data="{
    search: '{{ request('search') }}',
    timer: null,
    loading: false,
    selectedIds: [],
    selectAll: false,
    
    doSearch() {
        clearTimeout(this.timer);
        this.timer = setTimeout(() => {
            this.loading = true;
            fetch('{{ route('admin.catalogue.ajax-search') }}?search=' + encodeURIComponent(this.search))
                .then(response => response.json())
                .then(data => {
                    document.getElementById('productsTableContainer').innerHTML = data.html;
                    document.getElementById('productCount').textContent = data.total;
                    document.getElementById('paginationContainer').innerHTML = data.pagination;
                    this.loading = false;
                    this.selectedIds = [];
                    this.selectAll = false;
                })
                .catch(err => {
                    this.loading = false;
                    console.error(err);
                });
        }, 300);
    },
    
    toggleAll() {
        const checkboxes = document.querySelectorAll('.product-checkbox');
        if (this.selectAll) {
            this.selectedIds = Array.from(checkboxes).map(cb => parseInt(cb.value));
        } else {
            this.selectedIds = [];
        }
    },
    
    toggleOne(id) {
        if (this.selectedIds.includes(id)) {
            this.selectedIds = this.selectedIds.filter(i => i !== id);
        } else {
            this.selectedIds.push(id);
        }
        this.selectAll = this.selectedIds.length === document.querySelectorAll('.product-checkbox').length;
    },
    
    deleteSelected() {
        if (this.selectedIds.length === 0) return;
        if (!confirm('Delete ' + this.selectedIds.length + ' selected products?')) return;
        
        fetch('{{ route('admin.catalogue.bulk-delete') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ ids: this.selectedIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.doSearch();
                this.selectedIds = [];
                this.selectAll = false;
            }
        });
    }
}">
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
                <p class="text-gray-400 text-sm mt-1"><span id="productCount">{{ $products->total() }}</span> products in
                    catalogue</p>
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

        <!-- Search Bar - Responsive -->
        <div class="glass rounded-xl p-3 sm:p-4">
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                <div class="flex-1 relative">
                    <svg class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" x-model="search" @input="doSearch()" placeholder="Search by unique fields..."
                        class="w-full px-4 py-2.5 pl-10 rounded-xl bg-dark-300 border border-gray-700 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    <div x-show="loading" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                        <svg class="animate-spin h-5 w-5 text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button x-show="search.length > 0" @click="search = ''; doSearch()"
                        class="px-4 py-2.5 rounded-xl bg-gray-700 text-gray-300 hover:text-white hover:bg-gray-600 transition-colors flex items-center gap-2 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span class="hidden sm:inline">Clear</span>
                    </button>
                    <button x-show="selectedIds.length > 0" @click="deleteSelected()"
                        class="px-4 py-2.5 rounded-xl bg-red-600 text-white font-medium hover:bg-red-700 transition-colors flex items-center gap-2 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Delete (<span x-text="selectedIds.length"></span>)</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Products Table Container -->
        <div id="productsTableContainer">
            @include('admin.catalogue.partials.products-table')
        </div>

        <!-- Pagination -->
        <div id="paginationContainer">
            @if($products->hasPages())
                <div class="mt-6">
                    {{ $products->withQueryString()->links() }}
                </div>
            @endif
        </div>
    @endif
</div>