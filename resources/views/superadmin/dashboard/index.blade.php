@extends('superadmin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')
    <div class="space-y-6">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Clients -->
            <div class="stat-card glass rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Total Admins</p>
                        <h3 class="text-3xl font-bold text-white mt-1">{{ $stats['total_tenants'] }}</h3>
                        <p class="text-green-400 text-sm mt-2 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            {{ $stats['active_tenants'] }} active
                        </p>
                    </div>
                    <div
                        class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center glow-primary">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Revenue (This Month) -->
            <div class="stat-card glass rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Revenue (This Month)</p>
                        <h3 class="text-3xl font-bold text-white mt-1">₹{{ number_format($stats['this_month_revenue'], 0) }}
                        </h3>
                        <p class="text-gray-400 text-sm mt-2">
                            Total: ₹{{ number_format($stats['total_revenue'], 0) }}
                        </p>
                    </div>
                    <div
                        class="w-14 h-14 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center glow-success">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Leads -->
            <div class="stat-card glass rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Total Leads</p>
                        <h3 class="text-3xl font-bold text-white mt-1">{{ number_format($stats['total_leads']) }}</h3>
                        <p class="text-purple-400 text-sm mt-2">Across all admins</p>
                    </div>
                    <div
                        class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Messages -->
            <div class="stat-card glass rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Total Messages</p>
                        <h3 class="text-3xl font-bold text-white mt-1">{{ number_format($stats['total_messages']) }}</h3>
                        <p class="text-cyan-400 text-sm mt-2">WhatsApp chats</p>
                    </div>
                    <div
                        class="w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-500 flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Revenue Chart -->
            <div class="glass rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-white">Revenue Trend</h3>
                    <span class="text-sm text-gray-400">Last 7 days</span>
                </div>
                <canvas id="revenueChart" height="200"></canvas>
            </div>

            <!-- Subscription Distribution -->
            <div class="glass rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-white">Subscription Plans</h3>
                </div>
                <canvas id="subscriptionChart" height="200"></canvas>
            </div>
        </div>

        <!-- Recent Data -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Clients -->
            <div class="glass rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-white/10">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white">Recent Admins</h3>
                        <a href="{{ route('superadmin.admins.index') }}"
                            class="text-primary-400 hover:text-primary-300 text-sm">View all →</a>
                    </div>
                </div>
                <div class="divide-y divide-white/5">
                    @forelse($recentTenants as $tenant)
                        <div class="p-4 flex items-center justify-between hover:bg-white/5 transition-colors">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center">
                                    <span class="text-white font-medium">{{ substr($tenant->name, 0, 2) }}</span>
                                </div>
                                <div>
                                    <p class="text-white font-medium">{{ $tenant->name }}</p>
                                    <p class="text-gray-400 text-sm">{{ $tenant->email }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-lg 
                                                            {{ $tenant->subscription_plan === 'enterprise' ? 'bg-yellow-500/20 text-yellow-400' : '' }}
                                                            {{ $tenant->subscription_plan === 'pro' ? 'bg-purple-500/20 text-purple-400' : '' }}
                                                            {{ $tenant->subscription_plan === 'basic' ? 'bg-blue-500/20 text-blue-400' : '' }}
                                                            {{ $tenant->subscription_plan === 'free' ? 'bg-gray-500/20 text-gray-400' : '' }}">
                                    {{ ucfirst($tenant->subscription_plan) }}
                                </span>
                                <p class="text-gray-400 text-xs mt-1">{{ $tenant->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-400">
                            No admins yet
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Payments -->
            <div class="glass rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-white/10">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white">Recent Payments</h3>
                        <a href="{{ route('superadmin.payments.index') }}"
                            class="text-primary-400 hover:text-primary-300 text-sm">View all →</a>
                    </div>
                </div>
                <div class="divide-y divide-white/5">
                    @forelse($recentPayments as $payment)
                        <div class="p-4 flex items-center justify-between hover:bg-white/5 transition-colors">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="w-10 h-10 rounded-xl flex items-center justify-center
                                                            {{ $payment->status === 'success' ? 'bg-green-500/20' : 'bg-yellow-500/20' }}">
                                    @if($payment->status === 'success')
                                        <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-white font-medium">{{ $payment->admin->name ?? 'Unknown' }}</p>
                                    <p class="text-gray-400 text-sm">{{ ucfirst($payment->payment_method) }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-white font-semibold">{{ $payment->formatted_amount }}</p>
                                <p class="text-gray-400 text-xs">{{ $payment->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-400">
                            No payments yet
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="glass rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('superadmin.admins.create') }}"
                    class="glass-light rounded-xl p-4 text-center hover:bg-primary-500/10 transition-colors group">
                    <div
                        class="w-12 h-12 rounded-xl bg-primary-500/20 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <p class="text-white font-medium">Add Admin</p>
                </a>

                <a href="{{ route('superadmin.payments.index') }}"
                    class="glass-light rounded-xl p-4 text-center hover:bg-green-500/10 transition-colors group">
                    <div
                        class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <p class="text-white font-medium">View Payments</p>
                </a>

                <a href="{{ route('superadmin.credits.index') }}"
                    class="glass-light rounded-xl p-4 text-center hover:bg-yellow-500/10 transition-colors group">
                    <div
                        class="w-12 h-12 rounded-xl bg-yellow-500/20 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1" />
                        </svg>
                    </div>
                    <p class="text-white font-medium">Manage Credits</p>
                </a>

                <a href="{{ route('superadmin.settings.index') }}"
                    class="glass-light rounded-xl p-4 text-center hover:bg-purple-500/10 transition-colors group">
                    <div
                        class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <p class="text-white font-medium">Settings</p>
                </a>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            const revenueData = @json($revenueChart);

            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: Object.keys(revenueData),
                    datasets: [{
                        label: 'Revenue',
                        data: Object.values(revenueData),
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#6366f1',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            grid: { color: 'rgba(255,255,255,0.05)' },
                            ticks: { color: '#9ca3af' }
                        },
                        y: {
                            grid: { color: 'rgba(255,255,255,0.05)' },
                            ticks: { color: '#9ca3af' }
                        }
                    }
                }
            });

            // Subscription Chart
            const subCtx = document.getElementById('subscriptionChart').getContext('2d');
            const subData = @json($subscriptionDistribution);

            new Chart(subCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(subData).map(k => k.charAt(0).toUpperCase() + k.slice(1)),
                    datasets: [{
                        data: Object.values(subData),
                        backgroundColor: ['#6b7280', '#3b82f6', '#8b5cf6', '#f59e0b'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: '#9ca3af', padding: 20 }
                        }
                    },
                    cutout: '70%'
                }
            });
        </script>
    @endpush
@endsection