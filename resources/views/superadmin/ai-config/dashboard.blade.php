@extends('superadmin.layouts.app')

@section('title', 'AI Usage Dashboard')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-white">AI Usage Dashboard</h1>
                <p class="text-gray-400">Monitor token usage and costs across all tenants</p>
            </div>
            <div class="flex gap-2">
                <a href="?period=today"
                    class="px-3 py-2 rounded-lg {{ $period === 'today' ? 'bg-primary-500 text-white' : 'bg-gray-700 text-gray-300' }}">Today</a>
                <a href="?period=week"
                    class="px-3 py-2 rounded-lg {{ $period === 'week' ? 'bg-primary-500 text-white' : 'bg-gray-700 text-gray-300' }}">Week</a>
                <a href="?period=month"
                    class="px-3 py-2 rounded-lg {{ $period === 'month' ? 'bg-primary-500 text-white' : 'bg-gray-700 text-gray-300' }}">Month</a>
            </div>
        </div>

        <!-- Global Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="glass rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-white">{{ number_format($globalStats['total_requests'] ?? 0) }}
                        </p>
                        <p class="text-gray-400 text-sm">Total Requests</p>
                    </div>
                </div>
            </div>
            <div class="glass rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-white">{{ number_format($globalStats['total_tokens'] ?? 0) }}</p>
                        <p class="text-gray-400 text-sm">Total Tokens</p>
                    </div>
                </div>
            </div>
            <div class="glass rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-white">${{ number_format($globalStats['total_cost'] ?? 0, 4) }}
                        </p>
                        <p class="text-gray-400 text-sm">Total Cost (USD)</p>
                    </div>
                </div>
            </div>
            <div class="glass rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-lg bg-gradient-to-br from-yellow-500 to-orange-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-white">
                            {{ number_format($globalStats['avg_tokens_per_request'] ?? 0) }}</p>
                        <p class="text-gray-400 text-sm">Avg Tokens/Request</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Usage by Admin -->
            <div class="glass rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Usage by Tenant</h2>
                <div class="space-y-3">
                    @forelse($adminUsage as $usage)
                        <div class="flex items-center justify-between p-3 bg-dark-800/50 rounded-xl">
                            <div>
                                <p class="text-white font-medium">{{ $usage['admin_name'] }}</p>
                                <p class="text-gray-400 text-sm">{{ number_format($usage['requests']) }} requests</p>
                            </div>
                            <div class="text-right">
                                <p class="text-white">{{ number_format($usage['tokens']) }} tokens</p>
                                <p class="text-green-400 text-sm">${{ number_format($usage['cost'], 4) }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-400 text-center py-4">No usage data available</p>
                    @endforelse
                </div>
            </div>

            <!-- Usage by Model -->
            <div class="glass rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Usage by Model</h2>
                <div class="space-y-3">
                    @forelse($modelUsage as $usage)
                        <div class="flex items-center justify-between p-3 bg-dark-800/50 rounded-xl">
                            <div>
                                <p class="text-white font-medium">{{ $usage['model'] }}</p>
                                <p class="text-gray-400 text-sm">{{ ucfirst($usage['provider']) }} ·
                                    {{ number_format($usage['requests']) }} requests</p>
                            </div>
                            <div class="text-right">
                                <p class="text-white">{{ number_format($usage['tokens']) }} tokens</p>
                                <p class="text-green-400 text-sm">${{ number_format($usage['cost'], 4) }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-400 text-center py-4">No usage data available</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Daily Usage Chart -->
        <div class="glass rounded-2xl p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Daily Usage</h2>
            <div class="h-64 flex items-end gap-2">
                @php $maxTokens = collect($dailyUsage)->max('tokens') ?: 1; @endphp
                @foreach($dailyUsage as $day)
                    <div class="flex-1 flex flex-col items-center gap-2">
                        <div class="w-full bg-primary-500/20 rounded-t-lg transition-all hover:bg-primary-500/40"
                            style="height: {{ ($day['tokens'] / $maxTokens) * 100 }}%"
                            title="{{ number_format($day['tokens']) }} tokens">
                        </div>
                        <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($day['date'])->format('d') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Back to Config -->
        <div class="flex justify-start">
            <a href="{{ route('superadmin.ai-config.index') }}"
                class="px-4 py-2 text-gray-400 hover:text-white transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Configuration
            </a>
        </div>
    </div>
@endsection