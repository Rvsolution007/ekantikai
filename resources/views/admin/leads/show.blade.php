@extends('admin.layouts.app')

@section('title', 'Lead Details')
@section('page-title', 'Lead Details')

@section('content')
    <div class="p-4 lg:p-6">
        <!-- Header with Back Button -->
        <div class="glass rounded-2xl p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.leads.index') }}"
                        class="w-10 h-10 rounded-xl bg-white/5 hover:bg-white/10 flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-white">{{ $lead->contact_name ?? 'Unknown Lead' }}</h1>
                        <p class="text-gray-400">{{ $lead->contact_phone ?? '' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Lead Quality Badge -->
                    <div class="flex items-center gap-2 px-4 py-2 rounded-xl 
                                                                @if($lead->lead_quality === 'hot') bg-red-500/20 border border-red-500/30
                                                                @elseif($lead->lead_quality === 'warm') bg-yellow-500/20 border border-yellow-500/30
                                                                @else bg-blue-500/20 border border-blue-500/30 @endif">
                        <span class="text-xl">
                            @if($lead->lead_quality === 'hot') 🔥
                            @elseif($lead->lead_quality === 'warm') ☀️
                            @else ❄️ @endif
                        </span>
                        <div class="text-right">
                            <div class="text-lg font-bold text-white">{{ $lead->lead_score }}/100</div>
                            <div class="text-xs text-gray-400">{{ ucfirst($lead->lead_quality) }}</div>
                        </div>
                    </div>
                    <!-- Status Badge -->
                    <div class="px-4 py-2 rounded-xl bg-primary-500/20 border border-primary-500/30">
                        <span class="text-primary-300 font-medium">{{ $lead->stage ?? 'New Lead' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Contact Info Card -->
                <div class="glass rounded-2xl p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div
                            class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-500 to-purple-500 flex items-center justify-center">
                            <span class="text-2xl font-bold text-white">
                                {{ substr($lead->contact_name ?? 'U', 0, 1) }}
                            </span>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white">{{ $lead->contact_name ?? 'Unknown' }}</h3>
                            <p class="text-gray-400">{{ $lead->contact_phone }}</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between py-3 border-b border-white/10">
                            <span class="text-gray-400 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                City
                            </span>
                            <span class="text-white font-medium">{{ $lead->customer->global_fields['city'] ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-3 border-b border-white/10">
                            <span class="text-gray-400 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Purpose
                            </span>
                            <span class="text-white font-medium">{{ $lead->purpose_of_purchase ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-3 border-b border-white/10">
                            <span class="text-gray-400 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Bot Status
                            </span>
                            <span
                                class="px-2 py-1 text-xs rounded-full 
                                                                        {{ ($lead->customer->bot_enabled ?? true) ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                                {{ ($lead->customer->bot_enabled ?? true) ? '✓ Active' : '✗ Disabled' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-3">
                            <span class="text-gray-400 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                Products
                            </span>
                            <span class="px-2 py-1 text-sm rounded-full bg-primary-500/20 text-primary-300 font-medium">
                                @php
                                    $collectedCount = is_array($lead->collected_data['products'] ?? null) ? count($lead->collected_data['products']) : 0;
                                    $confirmCount = is_array($lead->product_confirmations ?? null) ? count($lead->product_confirmations) : 0;
                                    $leadProductCount = $lead->leadProducts ? $lead->leadProducts->count() : 0;
                                @endphp
                                {{ $collectedCount + $confirmCount + $leadProductCount }} items
                            </span>
                        </div>

                        @if($client)
                            <!-- Client Info Section -->
                            <div class="pt-3 mt-3 border-t border-white/10">
                                <p class="text-xs uppercase tracking-wider text-gray-500 mb-3">Client Details</p>
                                @if($client->business_name)
                                    <div class="flex items-center justify-between py-2">
                                        <span class="text-gray-400 text-sm">Business</span>
                                        <span class="text-white text-sm font-medium">{{ $client->business_name }}</span>
                                    </div>
                                @endif
                                @if($client->gst_number)
                                    <div class="flex items-center justify-between py-2">
                                        <span class="text-gray-400 text-sm">GST No.</span>
                                        <span class="text-white text-sm">{{ $client->gst_number }}</span>
                                    </div>
                                @endif
                                @if($client->email)
                                    <div class="flex items-center justify-between py-2">
                                        <span class="text-gray-400 text-sm">Email</span>
                                        <span class="text-white text-sm">{{ $client->email }}</span>
                                    </div>
                                @endif
                                @if($client->address)
                                    <div class="flex items-center justify-between py-2">
                                        <span class="text-gray-400 text-sm">Address</span>
                                        <span class="text-white text-sm text-right max-w-[60%]">{{ $client->address }}</span>
                                    </div>
                                @endif
                                <div class="mt-2">
                                    <a href="{{ route('admin.clients.show', $client) }}"
                                        class="text-primary-400 text-sm hover:text-primary-300 flex items-center gap-1">
                                        View Client Profile
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Lead Stage Card -->
                <div class="glass rounded-2xl p-6">
                    <h4 class="font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Lead Stage
                    </h4>
                    <div class="space-y-2">
                        @foreach(['New Lead' => 'yellow', 'Qualified' => 'blue', 'Confirm' => 'green', 'Lose' => 'red'] as $stageOption => $color)
                                        <button onclick="updateStage('{{ $stageOption }}')" class="w-full flex items-center p-3 rounded-xl transition-all
                                                                                                                                                                                                                                                        {{ $lead->stage === $stageOption
                            ? 'bg-' . $color . '-500/20 border-2 border-' . $color . '-500/50 text-' . $color . '-400'
                            : 'bg-white/5 border-2 border-transparent hover:bg-white/10 text-gray-400' }}">
                                            <span
                                                class="w-3 h-3 rounded-full mr-3 
                                                                                                                                                                                                                                                        @if($stageOption === 'New Lead') bg-yellow-500
                                                                                                                                                                                                                                                        @elseif($stageOption === 'Qualified') bg-blue-500
                                                                                                                                                                                                                                                        @elseif($stageOption === 'Confirm') bg-green-500
                                                                                                                                                                                                                                                        @else bg-red-500 @endif"></span>
                                            <span class="font-medium">{{ $stageOption }}</span>
                                            @if($lead->stage === $stageOption)
                                                <svg class="w-5 h-5 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @endif
                                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- Notes Card -->
                <div class="glass rounded-2xl p-6">
                    <h4 class="font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Notes
                    </h4>
                    <form action="{{ route('admin.leads.update', $lead) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <textarea name="notes" rows="4"
                            class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"
                            placeholder="Add notes about this lead...">{{ $lead->notes }}</textarea>
                        <button type="submit"
                            class="mt-3 w-full py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl transition-colors font-medium">
                            Save Notes
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Product Quotation Section -->
                <div class="glass rounded-2xl overflow-hidden">
                    <div class="bg-gradient-to-r from-primary-600 to-purple-600 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Product Quotation
                                </h3>
                                <p class="text-white/70 text-sm mt-1">Items requested by
                                    {{ $lead->contact_name ?? 'customer' }}
                                </p>
                            </div>
                            <div class="flex items-center gap-4">
                                <!-- DEBUG BUTTON -->
                                <button onclick="openDebugModal()"
                                    class="px-3 py-2 rounded-lg bg-yellow-500/20 border border-yellow-500/50 text-yellow-300 hover:bg-yellow-500/30 transition-colors text-sm font-medium flex items-center gap-2">
                                    🔍 Debug Data
                                </button>
                                <div class="text-right">
                                    <p class="text-white/70 text-sm">Date</p>
                                    <p class="text-white font-medium">{{ $lead->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @php
                        // Safely get products - handle multiple data formats
                        $allProducts = collect();

                        // 1. Get from lead_products table (new system)
                        try {
                            $leadProducts = $lead->leadProducts ?? collect();
                            foreach ($leadProducts as $lp) {
                                if (method_exists($lp, 'toProductArray')) {
                                    $productData = $lp->toProductArray();
                                    $productData['_source'] = 'lead_product';
                                    $productData['_id'] = $lp->id;
                                    $allProducts->push($productData);
                                }
                            }
                        } catch (\Exception $e) {
                            // Table might not exist yet
                        }

                        // 2. Transform OLD format product_confirmations (field/value pairs)
                        $legacyConfirmations = $lead->product_confirmations ?? [];
                        if (!empty($legacyConfirmations)) {
                            // Check if it's old format (has 'field' key)
                            $isOldFormat = isset($legacyConfirmations[0]['field']);

                            if ($isOldFormat) {
                                // Transform field/value pairs into single product object
                                $transformedProduct = [];
                                foreach ($legacyConfirmations as $item) {
                                    if (isset($item['field']) && isset($item['value'])) {
                                        $transformedProduct[$item['field']] = $item['value'];
                                    }
                                }
                                if (!empty($transformedProduct)) {
                                    $transformedProduct['_source'] = 'confirmation';
                                    $transformedProduct['_id'] = 0;
                                    $allProducts->push($transformedProduct);
                                }
                            } else {
                                // New format - just add as is
                                foreach ($legacyConfirmations as $idx => $lc) {
                                    if (is_array($lc)) {
                                        $lc['_source'] = 'confirmation';
                                        $lc['_id'] = $idx;
                                        $allProducts->push($lc);
                                    }
                                }
                            }
                        }

                        // 3. Get from collected_data (only workflow_questions - NOT global_questions)
                        $collectedData = $lead->collected_data ?? [];
                        $workflowQ = $collectedData['workflow_questions'] ?? [];

                        // Only add workflow questions as product if they have product fields
                        if (!empty($workflowQ)) {
                            // Only add if it has meaningful product data (category or model)
                            if (isset($workflowQ['category']) || isset($workflowQ['model'])) {
                                // Check if this data is already in allProducts
                                $isDuplicate = $allProducts->contains(function ($p) use ($workflowQ) {
                                    return ($p['category'] ?? '') === ($workflowQ['category'] ?? '') &&
                                        ($p['model'] ?? '') === ($workflowQ['model'] ?? '');
                                });
                                if (!$isDuplicate) {
                                    $workflowQ['_source'] = 'workflow';
                                    $workflowQ['_id'] = 'workflow';
                                    $allProducts->push($workflowQ);
                                }
                            }
                        }

                        // 4. Legacy collected_data products array
                        $collectedProducts = $collectedData['products'] ?? [];
                        foreach ($collectedProducts as $idx => $cp) {
                            if (is_array($cp)) {
                                $cp['_source'] = 'collected';
                                $cp['_id'] = $idx;
                                $allProducts->push($cp);
                            }
                        }

                        // Remove duplicates - keep unique by category+model
                        $allProducts = $allProducts->unique(function ($item) {
                            return ($item['category'] ?? '') . '|' . ($item['model'] ?? '');
                        })->values();
                    @endphp

                    @if($allProducts->count() > 0)
                        <div class="p-6">
                            <!-- Dynamic Quotation Table with productFields columns -->
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b border-white/10">
                                            @foreach($productFields as $field)
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">
                                                    {{ $field->display_name }}
                                                </th>
                                            @endforeach
                                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-400 uppercase">
                                                Action
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-white/5">
                                        @foreach($allProducts as $index => $product)
                                            <tr class="hover:bg-white/5 transition-colors" id="product-row-{{ $index }}">
                                                @foreach($productFields as $field)
                                                    <td class="px-4 py-4 text-white">
                                                        @php
                                                            $fieldName = $field->field_name;
                                                            $value = $product[$fieldName] ??
                                                                $product[strtolower($fieldName)] ??
                                                                $product[ucfirst($fieldName)] ??
                                                                null;

                                                            // Special handling for qty field - hide if it's 1 (default) or empty
                                                            if (in_array(strtolower($fieldName), ['qty', 'quantity'])) {
                                                                if (empty($value) || $value == 1) {
                                                                    $value = '-';
                                                                }
                                                            }
                                                        @endphp
                                                        {{ $value ?: '-' }}
                                                    </td>
                                                @endforeach
                                                <td class="px-4 py-4 text-center">
                                                    <div class="flex items-center justify-center gap-2">
                                                        <!-- Edit Button -->
                                                        <button type="button"
                                                            onclick="openEditProductModal({{ $index }}, '{{ $product['_source'] ?? 'collected' }}', '{{ $product['_id'] ?? $index }}', {{ json_encode($product) }})"
                                                            class="p-2 rounded-lg bg-blue-500/20 text-blue-400 hover:bg-blue-500/30 transition-colors"
                                                            title="Edit Product">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                        </button>
                                                        <!-- Delete Button -->
                                                        <button type="button"
                                                            onclick="confirmDeleteProduct('{{ $product['_source'] ?? 'collected' }}', '{{ $product['_id'] ?? $index }}')"
                                                            class="p-2 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-colors"
                                                            title="Delete Product">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Summary Footer -->
                            <div class="mt-6 p-4 bg-white/5 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-primary-500/20 flex items-center justify-center">
                                        <span class="text-lg">📦</span>
                                    </div>
                                    <div>
                                        <p class="text-white font-medium">Total Items</p>
                                        <p class="text-gray-400 text-sm">{{ $allProducts->count() }} product(s) selected</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-bold text-white">
                                        @php
                                            $totalQty = array_sum(array_map(function ($p) use ($productFields) {
                                                // Look for qty field - default to 0 if not mentioned
                                                foreach ($productFields as $field) {
                                                    if (in_array(strtolower($field->field_name), ['qty', 'quantity'])) {
                                                        $val = $p[$field->field_name] ?? $p['qty'] ?? $p['quantity'] ?? null;
                                                        return $val ? intval($val) : 0;
                                                    }
                                                }
                                                $val = $p['qty'] ?? $p['quantity'] ?? null;
                                                return $val ? intval($val) : 0;
                                            }, $allProducts->toArray()));
                                        @endphp
                                        {{ $totalQty }}
                                    </p>
                                    <p class="text-gray-400 text-sm">Total Quantity</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-12 text-center">
                            <div class="w-16 h-16 mx-auto rounded-2xl bg-white/5 flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <h3 class="text-white font-medium mb-2">No Products Yet</h3>
                            <p class="text-gray-400">Products will appear here once the customer selects them</p>
                        </div>
                    @endif
                </div>

                <!-- Customer Information from Client -->
                @if($client)
                    <div class="glass rounded-2xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-white flex items-center gap-2">
                                <span class="text-lg">🌐</span>
                                Customer Information
                            </h4>
                            <a href="{{ route('admin.clients.edit', $client) }}"
                                class="text-sm text-primary-400 hover:text-primary-300 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit
                            </a>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @if($client->name)
                                <div class="p-4 bg-white/5 rounded-xl">
                                    <p class="text-xs text-gray-400 mb-1">Name</p>
                                    <p class="text-white font-medium">{{ $client->name }}</p>
                                </div>
                            @endif
                            @if($client->phone)
                                <div class="p-4 bg-white/5 rounded-xl">
                                    <p class="text-xs text-gray-400 mb-1">Phone</p>
                                    <p class="text-white font-medium">{{ $client->phone }}</p>
                                </div>
                            @endif
                            @if($client->business_name)
                                <div class="p-4 bg-white/5 rounded-xl">
                                    <p class="text-xs text-gray-400 mb-1">Business Name</p>
                                    <p class="text-white font-medium">{{ $client->business_name }}</p>
                                </div>
                            @endif
                            @if($client->city)
                                <div class="p-4 bg-white/5 rounded-xl">
                                    <p class="text-xs text-gray-400 mb-1">City</p>
                                    <p class="text-white font-medium">{{ $client->city }}</p>
                                </div>
                            @endif
                            @if($client->state)
                                <div class="p-4 bg-white/5 rounded-xl">
                                    <p class="text-xs text-gray-400 mb-1">State</p>
                                    <p class="text-white font-medium">{{ $client->state }}</p>
                                </div>
                            @endif
                            @if($client->gst_number)
                                <div class="p-4 bg-white/5 rounded-xl">
                                    <p class="text-xs text-gray-400 mb-1">GST Number</p>
                                    <p class="text-white font-medium">{{ $client->gst_number }}</p>
                                </div>
                            @endif
                            @if($client->email)
                                <div class="p-4 bg-white/5 rounded-xl">
                                    <p class="text-xs text-gray-400 mb-1">Email</p>
                                    <p class="text-white font-medium">{{ $client->email }}</p>
                                </div>
                            @endif
                            @if($client->address)
                                <div class="p-4 bg-white/5 rounded-xl col-span-2">
                                    <p class="text-xs text-gray-400 mb-1">Address</p>
                                    <p class="text-white font-medium">{{ $client->address }}</p>
                                </div>
                            @endif
                            @if($client->notes)
                                <div class="p-4 bg-white/5 rounded-xl col-span-full">
                                    <p class="text-xs text-gray-400 mb-1">Notes</p>
                                    <p class="text-white font-medium">{{ $client->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <!-- No client yet - show option to create -->
                    <div class="glass rounded-2xl p-6">
                        <h4 class="font-semibold text-white mb-4 flex items-center gap-2">
                            <span class="text-lg">🌐</span>
                            Customer Information
                        </h4>
                        <div class="text-center py-8">
                            <div class="w-16 h-16 mx-auto rounded-full bg-white/5 flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <p class="text-gray-400 mb-4">No client profile created yet</p>
                            <a href="{{ route('admin.clients.create') }}?phone={{ $lead->contact_phone }}&name={{ urlencode($lead->contact_name ?? '') }}"
                                class="btn-gradient px-4 py-2 rounded-lg inline-flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Create Client Profile
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Chat History -->
                <div class="glass rounded-2xl p-6">
                    <h4 class="font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        Chat History
                    </h4>

                    <div class="max-h-80 overflow-y-auto space-y-3 mb-4" id="chat-container">
                        @forelse($chats as $chat)
                                        @php
                                            $role = is_object($chat) ? ($chat->role ?? 'bot') : 'bot';
                                            $content = is_object($chat) ? ($chat->content ?? $chat->message ?? '') : '';
                                            $createdAt = is_object($chat) ? ($chat->created_at ?? now()) : now();
                                            if (is_string($createdAt)) {
                                                $createdAt = \Carbon\Carbon::parse($createdAt);
                                            }
                                        @endphp
                                        <div class="flex {{ $role === 'user' ? 'justify-start' : 'justify-end' }}">
                                            <div class="max-w-xs lg:max-w-md px-4 py-3 rounded-2xl
                                                                                                                                                                                                                                                        {{ $role === 'user'
                            ? 'bg-white/10 text-white rounded-bl-none'
                            : 'bg-gradient-to-r from-primary-500 to-purple-500 text-white rounded-br-none' }}">
                                                <p class="text-sm">{{ $content }}</p>
                                                <p class="text-xs {{ $role === 'user' ? 'text-gray-400' : 'text-white/70' }} mt-1">
                                                    {{ $createdAt->format('M d, h:i A') }}
                                                </p>
                                            </div>
                                        </div>
                        @empty
                            <div class="text-center py-8">
                                <div class="w-12 h-12 mx-auto rounded-xl bg-white/5 flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </div>
                                <p class="text-gray-400">No messages yet</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Quick Reply -->
                    @if($lead->customer)
                        <form action="{{ route('admin.chats.send', $lead->customer) }}" method="POST" class="flex gap-2">
                            @csrf
                            <input type="text" name="message" placeholder="Type a message..."
                                class="flex-1 px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <button type="submit"
                                class="px-6 py-3 bg-gradient-to-r from-primary-500 to-purple-500 text-white rounded-xl hover:opacity-90 transition-opacity font-medium flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                Send
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function updateStage(stage) {
                fetch('{{ route("admin.leads.update-stage", $lead) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ stage: stage })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        }
                    });
            }

            // Auto scroll chat to bottom
            document.addEventListener('DOMContentLoaded', function () {
                const chatContainer = document.getElementById('chat-container');
                if (chatContainer) {
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }
            });

            // Delete product with passcode
            let deleteProductSource = null;
            let deleteProductId = null;

            function confirmDeleteProduct(source, id) {
                deleteProductSource = source;
                deleteProductId = id;
                document.getElementById('passcodeModal').classList.remove('hidden');
                document.getElementById('passcodeInput').value = '';
                document.getElementById('passcodeInput').focus();
                document.getElementById('passcodeError').classList.add('hidden');
            }

            function closePasscodeModal() {
                document.getElementById('passcodeModal').classList.add('hidden');
                deleteProductSource = null;
                deleteProductId = null;
            }

            function submitPasscode() {
                const passcode = document.getElementById('passcodeInput').value;
                if (!passcode) {
                    document.getElementById('passcodeError').textContent = 'Please enter passcode';
                    document.getElementById('passcodeError').classList.remove('hidden');
                    return;
                }

                fetch(`{{ url('admin/leads/' . $lead->id . '/product') }}/${deleteProductId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        passcode: passcode,
                        source: deleteProductSource
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            closePasscodeModal();
                            // Reload page to show updated data
                            location.reload();
                        } else {
                            document.getElementById('passcodeError').textContent = data.message || 'Invalid passcode';
                            document.getElementById('passcodeError').classList.remove('hidden');
                        }
                    })
                    .catch(error => {
                        document.getElementById('passcodeError').textContent = 'An error occurred. Please try again.';
                        document.getElementById('passcodeError').classList.remove('hidden');
                    });
            }
        </script>

        <!-- Edit Product Modal -->
        <div id="editProductModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="glass rounded-2xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white">✏️ Edit Product</h3>
                    <button onclick="closeEditProductModal()" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form id="editProductForm" onsubmit="saveProduct(event)">
                    <input type="hidden" id="editProductIndex" value="">
                    <input type="hidden" id="editProductSource" value="">
                    <input type="hidden" id="editProductId" value="">
                    <div id="editProductFields" class="space-y-4">
                        <!-- Dynamic fields will be inserted here -->
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button type="button" onclick="closeEditProductModal()"
                            class="flex-1 px-4 py-3 rounded-xl bg-gray-700 text-white font-medium hover:bg-gray-600 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-3 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700 transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // Product fields for edit modal
            const productFieldsList = @json($productFields->map(fn($f) => ['field_name' => $f->field_name, 'display_name' => $f->display_name])->values());

            function openEditProductModal(index, source, id, productData) {
                document.getElementById('editProductIndex').value = index;
                document.getElementById('editProductSource').value = source;
                document.getElementById('editProductId').value = id;

                // Build form fields dynamically
                const fieldsContainer = document.getElementById('editProductFields');
                fieldsContainer.innerHTML = '';

                productFieldsList.forEach(field => {
                    const fieldName = field.field_name;
                    const displayName = field.display_name;
                    let value = productData[fieldName] || productData[fieldName.toLowerCase()] || '';

                    // Skip internal fields
                    if (fieldName.startsWith('_')) return;

                    const fieldDiv = document.createElement('div');
                    fieldDiv.innerHTML = `
                                                <label class="block text-sm font-medium text-gray-300 mb-1">${displayName}</label>
                                                <input type="text" name="${fieldName}" value="${value}"
                                                    class="w-full px-4 py-3 rounded-xl bg-dark-300 border border-gray-700 text-white placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                            `;
                    fieldsContainer.appendChild(fieldDiv);
                });

                document.getElementById('editProductModal').classList.remove('hidden');
            }

            function closeEditProductModal() {
                document.getElementById('editProductModal').classList.add('hidden');
            }

            function saveProduct(event) {
                event.preventDefault();

                const source = document.getElementById('editProductSource').value;
                const productId = document.getElementById('editProductId').value;
                const form = document.getElementById('editProductForm');
                const formData = new FormData(form);

                // Build product data object
                const productData = {};
                formData.forEach((value, key) => {
                    if (!key.startsWith('edit')) {
                        productData[key] = value;
                    }
                });

                // Send update request
                fetch('{{ route("admin.leads.update-product", $lead->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        source: source,
                        product_id: productId,
                        product_data: productData
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            closeEditProductModal();
                            location.reload();
                        } else {
                            alert('Error: ' + (data.message || 'Failed to update product'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to update product');
                    });
            }
        </script>

        <!-- Passcode Modal -->
        <div id="passcodeModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="glass rounded-2xl p-6 w-full max-w-md mx-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white">🔐 Enter Delete Passcode</h3>
                    <button onclick="closePasscodeModal()" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <p class="text-gray-400 text-sm mb-4">Enter your delete passcode to confirm deletion of this product.</p>
                <input type="password" id="passcodeInput" placeholder="Enter passcode..."
                    class="w-full px-4 py-3 rounded-xl bg-dark-300 border border-gray-700 text-white placeholder-gray-500 focus:border-red-500 focus:ring-1 focus:ring-red-500 mb-2"
                    onkeypress="if(event.key==='Enter') submitPasscode()">
                <p id="passcodeError" class="text-red-400 text-sm mb-4 hidden"></p>
                <div class="flex gap-3">
                    <button onclick="closePasscodeModal()"
                        class="flex-1 px-4 py-3 rounded-xl bg-gray-700 text-white font-medium hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                    <button onclick="submitPasscode()"
                        class="flex-1 px-4 py-3 rounded-xl bg-red-600 text-white font-medium hover:bg-red-700 transition-colors">
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <!-- DEBUG MODAL -->
        <div id="debugModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/70 overflow-y-auto p-4">
            <div class="glass rounded-2xl p-6 w-full max-w-4xl mx-4 my-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white">🔍 Debug Data - Multi-Value Analysis</h3>
                    <button onclick="closeDebugModal()" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div id="debugContent" class="space-y-4">
                    <div class="text-center py-8">
                        <div class="animate-spin w-8 h-8 border-4 border-primary-500 border-t-transparent rounded-full mx-auto">
                        </div>
                        <p class="text-gray-400 mt-4">Loading debug data...</p>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button onclick="closeDebugModal()"
                        class="flex-1 px-4 py-3 rounded-xl bg-gray-700 text-white font-medium hover:bg-gray-600 transition-colors">
                        Close
                    </button>
                    <button onclick="forceRecreateProducts()" id="forceRecreateBtn"
                        class="flex-1 px-4 py-3 rounded-xl bg-orange-600 text-white font-medium hover:bg-orange-700 transition-colors flex items-center justify-center gap-2">
                        🔄 Force Recreate Products
                    </button>
                </div>
            </div>
        </div>

        <script>
            function openDebugModal() {
                document.getElementById('debugModal').classList.remove('hidden');
                loadDebugData();
            }

            function closeDebugModal() {
                document.getElementById('debugModal').classList.add('hidden');
            }

            function loadDebugData() {
                const leadId = {{ $lead->id }};
                fetch(`/api/debug/multi-value-test?lead_id=${leadId}`)
                    .then(response => response.json())
                    .then(data => {
                        displayDebugData(data);
                    })
                    .catch(error => {
                        document.getElementById('debugContent').innerHTML = `
                                    <div class="p-4 bg-red-500/20 border border-red-500/50 rounded-xl">
                                        <p class="text-red-400">Error loading debug data: ${error.message}</p>
                                    </div>
                                `;
                    });
            }

            function displayDebugData(data) {
                const content = document.getElementById('debugContent');

                // Diagnosis
                const diagnosis = data['4_diagnosis'] || {};
                const diagnosisClass = diagnosis.status?.includes('✅') ? 'bg-green-500/20 border-green-500/50' : 'bg-red-500/20 border-red-500/50';
                const diagnosisTextClass = diagnosis.status?.includes('✅') ? 'text-green-400' : 'text-red-400';

                let html = `
                            <!-- Diagnosis -->
                            <div class="p-4 ${diagnosisClass} border rounded-xl">
                                <h4 class="font-bold ${diagnosisTextClass} mb-2">📋 Diagnosis</h4>
                                <p class="${diagnosisTextClass}">${diagnosis.status || 'No diagnosis'}</p>
                                ${diagnosis.issues?.length > 0 ? `
                                    <ul class="mt-2 space-y-1">
                                        ${diagnosis.issues.map(issue => `<li class="text-sm text-red-300">• ${issue}</li>`).join('')}
                                    </ul>
                                ` : ''}
                                ${diagnosis.recommendation ? `<p class="mt-2 text-yellow-300 text-sm">💡 ${diagnosis.recommendation}</p>` : ''}
                            </div>

                            <!-- workflow_questions -->
                            <div class="p-4 bg-blue-500/20 border border-blue-500/50 rounded-xl">
                                <h4 class="font-bold text-blue-400 mb-2">1️⃣ workflow_questions (flowchart progress)</h4>
                                <p class="text-gray-400 text-xs mb-2">${data['1_workflow_questions']?.description || ''}</p>
                                <pre class="text-sm text-white bg-black/30 p-3 rounded-lg overflow-x-auto">${JSON.stringify(data['1_workflow_questions']?.data || {}, null, 2)}</pre>
                            </div>

                            <!-- product_confirmations -->
                            <div class="p-4 bg-purple-500/20 border border-purple-500/50 rounded-xl">
                                <h4 class="font-bold text-purple-400 mb-2">2️⃣ product_confirmations (AI output) - ${data['2_product_confirmations']?.count || 0} items</h4>
                                <p class="text-gray-400 text-xs mb-2">${data['2_product_confirmations']?.description || ''}</p>
                                <pre class="text-sm text-white bg-black/30 p-3 rounded-lg overflow-x-auto max-h-48">${JSON.stringify(data['2_product_confirmations']?.data || [], null, 2)}</pre>
                            </div>

                            <!-- LeadProducts -->
                            <div class="p-4 bg-green-500/20 border border-green-500/50 rounded-xl">
                                <h4 class="font-bold text-green-400 mb-2">3️⃣ lead_products (Product Quotation) - ${data['3_current_lead_products']?.count || 0} rows</h4>
                                <p class="text-gray-400 text-xs mb-2">${data['3_current_lead_products']?.description || ''}</p>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-white">
                                        <thead class="border-b border-white/20">
                                            <tr>
                                                <th class="py-2 px-2 text-left">ID</th>
                                                <th class="py-2 px-2 text-left">Category</th>
                                                <th class="py-2 px-2 text-left">Model</th>
                                                <th class="py-2 px-2 text-left">Size</th>
                                                <th class="py-2 px-2 text-left">Finish</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${(data['3_current_lead_products']?.data || []).map(p => `
                                                <tr class="border-b border-white/10">
                                                    <td class="py-2 px-2">${p.id}</td>
                                                    <td class="py-2 px-2 ${p.category?.includes(',') ? 'text-red-400 font-bold' : ''}">${p.category || '-'}</td>
                                                    <td class="py-2 px-2">${p.model || '-'}</td>
                                                    <td class="py-2 px-2">${p.size || '-'}</td>
                                                    <td class="py-2 px-2">${p.finish || '-'}</td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        `;

                content.innerHTML = html;
            }

            function forceRecreateProducts() {
                const leadId = {{ $lead->id }};
                const btn = document.getElementById('forceRecreateBtn');
                btn.disabled = true;
                btn.innerHTML = '<span class="animate-spin">⏳</span> Processing...';

                fetch(`/api/debug/force-recreate?lead_id=${leadId}`)
                    .then(response => response.json())
                    .then(data => {
                        alert(`Done! Deleted ${data.deleted_count} old rows, created ${data.new_products_created} new rows.`);
                        location.reload();
                    })
                    .catch(error => {
                        alert('Error: ' + error.message);
                        btn.disabled = false;
                        btn.innerHTML = '🔄 Force Recreate Products';
                    });
            }
        </script>
    @endpush
@endsection