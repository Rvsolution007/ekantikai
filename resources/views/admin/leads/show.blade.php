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
                            <div class="text-right">
                                <p class="text-white/70 text-sm">Date</p>
                                <p class="text-white font-medium">{{ $lead->created_at->format('M d, Y') }}</p>
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
                                    $allProducts->push($lp->toProductArray());
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
                                    $allProducts->push($transformedProduct);
                                }
                            } else {
                                // New format - just add as is
                                foreach ($legacyConfirmations as $lc) {
                                    if (is_array($lc)) {
                                        $allProducts->push($lc);
                                    }
                                }
                            }
                        }

                        // 3. Get from collected_data (global_questions + workflow_questions)
                        $collectedData = $lead->collected_data ?? [];
                        $globalQ = $collectedData['global_questions'] ?? [];
                        $workflowQ = $collectedData['workflow_questions'] ?? [];

                        // Merge global and workflow questions as a product if they have product fields
                        if (!empty($globalQ) || !empty($workflowQ)) {
                            $mergedProduct = array_merge($globalQ, $workflowQ);
                            // Only add if it has meaningful product data
                            if (isset($mergedProduct['category']) || isset($mergedProduct['model'])) {
                                // Check if this data is already in allProducts
                                $isDuplicate = $allProducts->contains(function ($p) use ($mergedProduct) {
                                    return ($p['category'] ?? '') === ($mergedProduct['category'] ?? '') &&
                                        ($p['model'] ?? '') === ($mergedProduct['model'] ?? '');
                                });
                                if (!$isDuplicate) {
                                    $allProducts->push($mergedProduct);
                                }
                            }
                        }

                        // 4. Legacy collected_data products array
                        $collectedProducts = $collectedData['products'] ?? [];
                        foreach ($collectedProducts as $cp) {
                            if (is_array($cp)) {
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
                                                            $value = $product[$field->field_name] ??
                                                                $product[strtolower($field->field_name)] ??
                                                                $product[ucfirst($field->field_name)] ??
                                                                '-';
                                                        @endphp
                                                        {{ $value ?: '-' }}
                                                    </td>
                                                @endforeach
                                                <td class="px-4 py-4 text-center">
                                                    <button type="button"
                                                        onclick="confirmDeleteProduct('{{ $product['_source'] ?? 'collected' }}', '{{ $product['_id'] ?? $index }}')"
                                                        class="p-2 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-colors"
                                                        title="Delete Product">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
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
                                                // Look for qty field
                                                foreach ($productFields as $field) {
                                                    if (in_array(strtolower($field->field_name), ['qty', 'quantity'])) {
                                                        return intval($p[$field->field_name] ?? $p['qty'] ?? $p['quantity'] ?? 1);
                                                    }
                                                }
                                                return intval($p['qty'] ?? $p['quantity'] ?? 1);
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

                <!-- Global Information -->
                @php
                    $globalQuestions = $lead->collected_data['global_questions'] ?? [];
                @endphp
                @if(!empty($globalQuestions))
                    <div class="glass rounded-2xl p-6">
                        <h4 class="font-semibold text-white mb-4 flex items-center gap-2">
                            <span class="text-lg">🌐</span>
                            Customer Information
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($globalQuestions as $key => $value)
                                <div class="p-4 bg-white/5 rounded-xl">
                                    <p class="text-xs text-gray-400 mb-1">{{ ucwords(str_replace('_', ' ', $key)) }}</p>
                                    <p class="text-white font-medium">{{ $value }}</p>
                                </div>
                            @endforeach
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
    @endpush
@endsection