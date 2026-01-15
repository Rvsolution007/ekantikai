@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="stat-card glass rounded-2xl p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Total Leads</p>
                        <h3 class="text-2xl font-bold text-white mt-1">{{ $totalLeads ?? 0 }}</h3>
                    </div>
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="stat-card glass rounded-2xl p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">New Leads</p>
                        <h3 class="text-2xl font-bold text-green-400 mt-1">{{ $newLeads ?? 0 }}</h3>
                    </div>
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="stat-card glass rounded-2xl p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Qualified</p>
                        <h3 class="text-2xl font-bold text-yellow-400 mt-1">{{ $qualifiedLeads ?? 0 }}</h3>
                    </div>
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-500 to-orange-500 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="stat-card glass rounded-2xl p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Confirmed</p>
                        <h3 class="text-2xl font-bold text-purple-400 mt-1">{{ $confirmedLeads ?? 0 }}</h3>
                    </div>
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="stat-card glass rounded-2xl p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Today's Chats</p>
                        <h3 class="text-2xl font-bold text-cyan-400 mt-1">{{ $todayChats ?? 0 }}</h3>
                    </div>
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-500 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="glass rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-white">Lead Trends (Last 7 Days)</h3>
                </div>
                <canvas id="leadTrendChart" height="200"></canvas>
            </div>

            <div class="glass rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-white mb-6">Popular Products</h3>
                <div class="space-y-4">
                    @forelse($popularProducts ?? [] as $product)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-300">{{ $product->name }}</span>
                            <span class="text-primary-400 font-semibold">{{ $product->count }}</span>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-4">No product data yet</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Data -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="glass rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-white/10 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white">Recent Leads</h3>
                    <a href="{{ route('admin.leads.index') }}" class="text-primary-400 hover:text-primary-300 text-sm">View
                        All →</a>
                </div>
                <div class="divide-y divide-white/5">
                    @forelse($recentLeads ?? [] as $lead)
                        <div class="p-4 flex items-center justify-between hover:bg-white/5 transition-colors">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center">
                                    <span class="text-white font-medium">{{ substr($lead->name ?? 'L', 0, 1) }}</span>
                                </div>
                                <div>
                                    <p class="text-white font-medium">{{ $lead->name ?? 'Unknown' }}</p>
                                    <p class="text-gray-400 text-sm">{{ $lead->phone ?? '' }}</p>
                                </div>
                            </div>
                            <span
                                class="px-2 py-1 text-xs rounded-lg bg-blue-500/20 text-blue-400">{{ ucfirst($lead->stage ?? 'new') }}</span>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-400">No leads yet</div>
                    @endforelse
                </div>
            </div>

            <div class="glass rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-white/10 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white">Recent Messages</h3>
                    <a href="{{ route('admin.chats.index') }}" class="text-primary-400 hover:text-primary-300 text-sm">View
                        All →</a>
                </div>
                <div class="divide-y divide-white/5">
                    @forelse($recentMessages ?? [] as $message)
                        <div class="p-4 hover:bg-white/5 transition-colors">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-white font-medium">{{ $message->whatsappUser->name ?? 'Unknown' }}</span>
                                <span class="text-gray-500 text-xs">{{ $message->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-gray-400 text-sm truncate">{{ $message->message ?? '' }}</p>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-400">No messages yet</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const ctx = document.getElementById('leadTrendChart').getContext('2d');
            const chartData = @json($chartData ?? ['labels' => [], 'data' => []]);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels || ['Jan 05', 'Jan 06', 'Jan 07', 'Jan 08', 'Jan 09', 'Jan 10', 'Jan 11'],
                    datasets: [
                        { label: 'New Leads', data: chartData.newLeads || [0, 0, 0, 0, 0, 0, 0], borderColor: '#22c55e', backgroundColor: 'rgba(34, 197, 94, 0.1)', tension: 0.4, fill: true },
                        { label: 'Qualified', data: chartData.qualified || [0, 0, 0, 0, 0, 0, 0], borderColor: '#eab308', backgroundColor: 'rgba(234, 179, 8, 0.1)', tension: 0.4, fill: true },
                        { label: 'Confirmed', data: chartData.confirmed || [0, 0, 0, 0, 0, 0, 0], borderColor: '#6366f1', backgroundColor: 'rgba(99, 102, 241, 0.1)', tension: 0.4, fill: true }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom', labels: { color: '#9ca3af' } } },
                    scales: {
                        x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9ca3af' } },
                        y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9ca3af' } }
                    }
                }
            });
        </script>
    @endpush
@endsection