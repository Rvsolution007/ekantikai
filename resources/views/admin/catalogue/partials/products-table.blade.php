@if($products->isEmpty())
    <!-- Empty State -->
    <div class="glass rounded-2xl p-12 text-center">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>
        <h3 class="text-lg font-medium text-white mb-2">No Products Found</h3>
        <p class="text-gray-400 mb-6">Try a different search term or add new products.</p>
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
                                @change="selectAll = $event.target.checked; toggleAll()" :checked="selectAll">
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
                            <th class="px-4 py-4 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Image
                            </th>
                        @endif
                        <th class="px-4 py-4 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Status
                        </th>
                        <th class="px-4 py-4 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($products as $index => $product)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <input type="checkbox"
                                    class="product-checkbox w-4 h-4 rounded bg-dark-300 border-gray-700 text-primary-500 focus:ring-primary-500"
                                    value="{{ $product->id }}" @change="toggleOne({{ $product->id }})"
                                    :checked="selectedIds.includes({{ $product->id }})">
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
                                            <img src="{{ $product->image_url }}" alt="Product"
                                                class="w-10 h-10 rounded-lg object-cover">
                                            <form action="{{ route('admin.catalogue.upload-image', $product) }}" method="POST"
                                                enctype="multipart/form-data" class="inline">
                                                @csrf
                                                <label
                                                    class="cursor-pointer p-1 rounded bg-blue-500/20 text-blue-400 hover:bg-blue-500/30">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    <input type="file" name="image" accept="image/*" class="hidden"
                                                        onchange="this.form.submit()">
                                                </label>
                                            </form>
                                        </div>
                                    @else
                                        <form action="{{ route('admin.catalogue.upload-image', $product) }}" method="POST"
                                            enctype="multipart/form-data" class="inline">
                                            @csrf
                                            <label
                                                class="cursor-pointer px-3 py-1 rounded-lg bg-primary-500/20 text-primary-400 hover:bg-primary-500/30 text-xs font-medium inline-flex items-center space-x-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                                </svg>
                                                <span>Upload</span>
                                                <input type="file" name="image" accept="image/*" class="hidden"
                                                    onchange="this.form.submit()">
                                            </label>
                                        </form>
                                    @endif
                                </td>
                            @endif
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                <span
                                    class="px-3 py-1 text-xs rounded-lg {{ $product->is_active ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400' }}">
                                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <form action="{{ route('admin.catalogue.toggle-status', $product) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="p-2 rounded-lg {{ $product->is_active ? 'bg-yellow-500/20 text-yellow-400 hover:bg-yellow-500/30' : 'bg-green-500/20 text-green-400 hover:bg-green-500/30' }} transition-colors"
                                            title="{{ $product->is_active ? 'Deactivate' : 'Activate' }}">
                                            @if($product->is_active)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                            @endif
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.catalogue.destroy', $product) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Delete this product?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="p-2 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
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
@endif