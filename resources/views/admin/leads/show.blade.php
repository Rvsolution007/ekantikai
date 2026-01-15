@extends('admin.layouts.app')

@section('title', 'Lead Details')
@section('page-title', 'Lead Details')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Lead Info Card -->
    <div class="lg:col-span-1 space-y-6">
        <!-- User Info -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center mb-4">
                <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center">
                    <span class="text-2xl font-bold text-primary-600">
                        {{ substr($lead->contact_name ?? 'U', 0, 1) }}
                    </span>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-800">
                        {{ $lead->contact_name ?? 'Unknown User' }}
                    </h3>
                    <p class="text-gray-500">{{ $lead->contact_phone ?? '' }}</p>
                </div>
            </div>

            <div class="space-y-3 border-t pt-4">
                <div class="flex justify-between">
                    <span class="text-gray-500">City</span>
                    <span class="text-gray-800">{{ $lead->customer->global_fields['city'] ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Purpose</span>
                    <span class="text-gray-800">{{ $lead->purpose_of_purchase ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Bot Status</span>
                    <span class="px-2 py-1 text-xs rounded-full
                        {{ ($lead->customer->bot_enabled ?? true) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ ($lead->customer->bot_enabled ?? true) ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Products</span>
                    <span class="text-gray-800">{{ count($lead->collected_data['products'] ?? []) }}</span>
                </div>
            </div>
        </div>

        <!-- Lead Stage -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h4 class="font-semibold text-gray-800 mb-4">Lead Stage</h4>
            <div x-data="{ stage: '{{ $lead->stage }}' }">
                <div class="space-y-2">
                    @foreach(['New Lead', 'Qualified', 'Confirm', 'Lose'] as $stageOption)
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors
                        {{ $lead->stage === $stageOption ? 'border-primary-500 bg-primary-50' : 'hover:bg-gray-50' }}">
                        <input type="radio" name="stage" value="{{ $stageOption }}" 
                               {{ $lead->stage === $stageOption ? 'checked' : '' }}
                               @change="updateStage('{{ $stageOption }}')"
                               class="text-primary-600">
                        <span class="ml-3 text-sm font-medium">{{ $stageOption }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Lead Quality -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h4 class="font-semibold text-gray-800 mb-4">Lead Quality</h4>
            <div class="flex items-center justify-between">
                <span class="text-3xl">
                    @if($lead->lead_quality === 'hot') 🔥
                    @elseif($lead->lead_quality === 'warm') ☀️
                    @elseif($lead->lead_quality === 'cold') ❄️
                    @else ⚠️ @endif
                </span>
                <div class="text-right">
                    <p class="text-2xl font-bold text-gray-800">{{ $lead->lead_score }}/100</p>
                    <p class="text-sm text-gray-500">{{ ucfirst($lead->lead_quality) }}</p>
                </div>
            </div>
            <div class="mt-3 bg-gray-200 rounded-full h-2">
                <div class="h-2 rounded-full
                    @if($lead->lead_quality === 'hot') bg-red-500
                    @elseif($lead->lead_quality === 'warm') bg-yellow-500
                    @else bg-blue-500 @endif"
                    style="width: {{ $lead->lead_score }}%"></div>
            </div>
        </div>
    </div>

    <!-- Products & Chat -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Products -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h4 class="font-semibold text-gray-800 mb-4">Selected Products</h4>
            @if($lead->products->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Product</th>
                            <th class="px-4 py-2 text-left">Model</th>
                            <th class="px-4 py-2 text-left">Size</th>
                            <th class="px-4 py-2 text-left">Finish</th>
                            <th class="px-4 py-2 text-left">Qty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($lead->products as $product)
                        <tr>
                            <td class="px-4 py-2">{{ $product->product ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $product->model ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $product->size ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $product->finish ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $product->qty ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-gray-500 text-center py-4">No products selected yet</p>
            @endif
        </div>

        <!-- Collected Data from Workflow -->
        @if(!empty($lead->collected_data))
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h4 class="font-semibold text-gray-800 mb-4">📋 Collected Information</h4>
            
            @php
                $collectedData = $lead->collected_data;
                $workflowQuestions = $collectedData['workflow_questions'] ?? [];
                $globalQuestions = $collectedData['global_questions'] ?? [];
                $products = $collectedData['products'] ?? [];
            @endphp

            <!-- Workflow Questions -->
            @if(!empty($workflowQuestions))
            <div class="mb-6">
                <h5 class="text-sm font-semibold text-gray-700 mb-3">💬 Workflow Answers</h5>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($workflowQuestions as $key => $value)
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 mb-1">{{ ucwords(str_replace('_', ' ', $key)) }}</p>
                        <p class="text-sm font-medium text-gray-800">{{ $value }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Global Questions -->
            @if(!empty($globalQuestions))
            <div class="mb-6">
                <h5 class="text-sm font-semibold text-gray-700 mb-3">🌐 Global Information</h5>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($globalQuestions as $key => $value)
                    <div class="bg-blue-50 rounded-lg p-3">
                        <p class="text-xs text-blue-600 mb-1">{{ ucwords(str_replace('_', ' ', $key)) }}</p>
                        <p class="text-sm font-medium text-gray-800">{{ $value }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Products from Collected Data -->
            @if(!empty($products))
            <div>
                <h5 class="text-sm font-semibold text-gray-700 mb-3">📦 Product Details</h5>
                <div class="space-y-3">
                    @foreach($products as $index => $product)
                    <div class="bg-green-50 rounded-lg p-3">
                        <p class="text-xs text-green-600 mb-2">Product {{ $index + 1 }}</p>
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            @foreach($product as $field => $value)
                            <div>
                                <span class="text-gray-500">{{ ucwords(str_replace('_', ' ', $field)) }}:</span>
                                <span class="font-medium text-gray-800">{{ $value }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Chat History -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h4 class="font-semibold text-gray-800 mb-4">Chat History</h4>
            <div class="max-h-96 overflow-y-auto space-y-3" id="chat-container">
                @forelse($chats as $chat)
                @php
                    // Handle both Eloquent models and stdClass objects
                    $role = is_object($chat) ? ($chat->role ?? 'bot') : 'bot';
                    $content = is_object($chat) ? ($chat->content ?? $chat->message ?? '') : '';
                    $createdAt = is_object($chat) ? ($chat->created_at ?? now()) : now();
                    
                    // Convert string to Carbon if needed
                    if (is_string($createdAt)) {
                        $createdAt = \Carbon\Carbon::parse($createdAt);
                    }
                @endphp
                <div class="flex {{ $role === 'user' ? 'justify-start' : 'justify-end' }}">
                    <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg
                        {{ $role === 'user' ? 'bg-gray-100 text-gray-800' : 'bg-primary-500 text-white' }}">
                        <p class="text-sm">{{ $content }}</p>
                        <p class="text-xs {{ $role === 'user' ? 'text-gray-500' : 'text-primary-100' }} mt-1">
                            {{ $createdAt->format('M d, h:i A') }}
                        </p>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">No messages yet</p>
                @endforelse
            </div>

            <!-- Quick Reply -->
            @if($lead->customer)
            <form action="{{ route('admin.chats.send', $lead->customer) }}" method="POST" class="mt-4 flex gap-2">
                @csrf
                <input type="text" name="message" placeholder="Type a message..." 
                       class="flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    Send
                </button>
            </form>
            @endif
        </div>

        <!-- Notes -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h4 class="font-semibold text-gray-800 mb-4">Notes</h4>
            <form action="{{ route('admin.leads.update', $lead) }}" method="POST">
                @csrf
                @method('PUT')
                <textarea name="notes" rows="3" 
                          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500"
                          placeholder="Add notes about this lead...">{{ $lead->notes }}</textarea>
                <button type="submit" class="mt-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Save Notes
                </button>
            </form>
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
</script>
@endpush
@endsection
