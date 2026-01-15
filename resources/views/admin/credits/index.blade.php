@extends('admin.layouts.app')

@section('title', 'Credits')
@section('page-title', 'Credit Management')

@section('content')
    <div class="space-y-6">
        <!-- Credit Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="stat-card glass rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Total Credits</p>
                        <h3 class="text-3xl font-bold text-white mt-1">{{ number_format($credit->total_credits ?? 0) }}</h3>
                    </div>
                    <div
                        class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="stat-card glass rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Used Credits</p>
                        <h3 class="text-3xl font-bold text-red-400 mt-1">{{ number_format($credit->used_credits ?? 0) }}
                        </h3>
                    </div>
                    <div
                        class="w-14 h-14 rounded-2xl bg-gradient-to-br from-red-500 to-rose-500 flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="stat-card glass rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Available Credits</p>
                        <h3 class="text-3xl font-bold text-green-400 mt-1">
                            {{ number_format($credit->available_credits ?? 0) }}</h3>
                    </div>
                    <div
                        class="w-14 h-14 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usage Progress -->
        <div class="glass rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-white">Credit Usage</h3>
                <span class="text-gray-400">{{ $credit->usage_percentage ?? 0 }}% used</span>
            </div>
            <div class="w-full h-4 bg-dark-200 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-primary-500 to-purple-500 transition-all duration-500"
                    style="width: {{ min(100, $credit->usage_percentage ?? 0) }}%"></div>
            </div>
            @if(($credit->usage_percentage ?? 0) > 80)
                <p class="text-yellow-400 text-sm mt-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Low credits! Contact your administrator to add more credits.
                </p>
            @endif
        </div>

        <!-- Credit Usage Breakdown -->
        <div class="glass rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <h3 class="text-lg font-semibold text-white">Usage Breakdown</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="glass-light rounded-xl p-4">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-gray-400">AI Calls</span>
                        <span class="text-white font-semibold">{{ number_format($credit->ai_calls_count ?? 0) }}</span>
                    </div>
                    <div class="w-full h-2 bg-dark-200 rounded-full overflow-hidden">
                        <div class="h-full bg-purple-500" style="width: 60%"></div>
                    </div>
                </div>
                <div class="glass-light rounded-xl p-4">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-gray-400">Messages Sent</span>
                        <span class="text-white font-semibold">{{ number_format($credit->messages_count ?? 0) }}</span>
                    </div>
                    <div class="w-full h-2 bg-dark-200 rounded-full overflow-hidden">
                        <div class="h-full bg-green-500" style="width: 40%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Usage -->
        <div class="glass rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <h3 class="text-lg font-semibold text-white">Recent Credit Usage</h3>
            </div>
            <table class="w-full">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Description</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-400 uppercase">Credits</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($recentUsage ?? [] as $usage)
                        <tr class="table-row">
                            <td class="px-6 py-4 text-gray-300">{{ $usage->created_at->format('d M Y, h:i A') }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-3 py-1 text-xs font-medium rounded-lg {{ $usage->type === 'ai_call' ? 'bg-purple-500/20 text-purple-400' : 'bg-green-500/20 text-green-400' }}">
                                    {{ ucfirst(str_replace('_', ' ', $usage->type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-400">{{ $usage->description ?? '-' }}</td>
                            <td class="px-6 py-4 text-right text-red-400 font-medium">-{{ $usage->credits }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400">No usage history yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection