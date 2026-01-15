@extends('admin.layouts.app')

@section('title', isset($catalogue) ? 'Edit Catalogue' : 'Add Catalogue')
@section('page-title', isset($catalogue) ? 'Edit Catalogue Item' : 'Add New Catalogue Item')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form action="{{ isset($catalogue) ? route('admin.catalogue.update', $catalogue) : route('admin.catalogue.store') }}" method="POST">
            @csrf
            @if(isset($catalogue))
                @method('PUT')
            @endif

            <div class="space-y-4">
                <!-- Product Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Type *</label>
                    <select name="product_type" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="">Select Type</option>
                        @foreach($productTypes as $type)
                        <option value="{{ $type }}" {{ old('product_type', $catalogue->product_type ?? '') == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                        @endforeach
                    </select>
                    @error('product_type')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Model Code -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Model Code *</label>
                    <input type="text" name="model_code" value="{{ old('model_code', $catalogue->model_code ?? '') }}" required
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500"
                           placeholder="e.g., 007, 9005, 028">
                    @error('model_code')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Sizes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sizes (comma-separated)</label>
                    <input type="text" name="sizes" value="{{ old('sizes', $catalogue->sizes ?? '') }}"
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500"
                           placeholder="e.g., 96mm, 128mm, 160mm">
                </div>

                <!-- Finishes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Finishes (comma-separated)</label>
                    <input type="text" name="finishes" value="{{ old('finishes', $catalogue->finishes ?? '') }}"
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500"
                           placeholder="e.g., Chrome, Matt Black, Gold">
                </div>

                <!-- Material -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Material</label>
                    <input type="text" name="material" value="{{ old('material', $catalogue->material ?? '') }}"
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500"
                           placeholder="e.g., Zinc Alloy, Aluminium">
                </div>

                <!-- Pack Per Size -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pack Per Size</label>
                    <input type="text" name="pack_per_size" value="{{ old('pack_per_size', $catalogue->pack_per_size ?? '') }}"
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500"
                           placeholder="e.g., 10 pcs/box">
                </div>

                <!-- Base Price -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Base Price (₹)</label>
                    <input type="number" name="base_price" step="0.01" min="0"
                           value="{{ old('base_price', $catalogue->base_price ?? '') }}"
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500"
                           placeholder="0.00">
                </div>

                <!-- Image URL -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                    <input type="url" name="image_url" value="{{ old('image_url', $catalogue->image_url ?? '') }}"
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500"
                           placeholder="https://...">
                </div>

                <!-- Active Status -->
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           {{ old('is_active', $catalogue->is_active ?? true) ? 'checked' : '' }}
                           class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <label for="is_active" class="ml-2 text-sm text-gray-700">Active</label>
                </div>
            </div>

            <div class="mt-6 flex gap-2">
                <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    {{ isset($catalogue) ? 'Update' : 'Create' }}
                </button>
                <a href="{{ route('admin.catalogue.index') }}" 
                   class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
