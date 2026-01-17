@extends('superadmin.layouts.app')

@section('title', 'View Admin')

@section('content')
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white mb-2">{{ $tenant->name }}</h1>
                <p class="text-gray-400">Admin Details & Statistics</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('superadmin.admins.edit', $tenant) }}"
                    class="btn-gradient px-4 py-2 rounded-lg inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Admin
                </a>
                <a href="{{ route('superadmin.admins.index') }}"
                    class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Admins
                </a>
            </div>
        </div>
    </div>

    <!-- Client Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="glass-card p-6 rounded-xl">
            <div class="flex items-center justify-between mb-4">
                <span class="text-gray-400 text-sm">Status</span>
                @if($tenant->status === 'active')
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400">Active</span>
                @elseif($tenant->status === 'trial')
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-400">Trial</span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-red-500/20 text-red-400">Inactive</span>
                @endif
            </div>
            <p class="text-2xl font-bold text-white capitalize">{{ $tenant->status }}</p>
        </div>

        <div class="glass-card p-6 rounded-xl">
            <div class="flex items-center justify-between mb-4">
                <span class="text-gray-400 text-sm">Subscription Plan</span>
                <div class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-white capitalize">{{ $tenant->subscription_plan ?? 'Free' }}</p>
        </div>

        <div class="glass-card p-6 rounded-xl">
            <div class="flex items-center justify-between mb-4">
                <span class="text-gray-400 text-sm">Available Credits</span>
                <div class="w-10 h-10 rounded-lg bg-green-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-white">{{ number_format($tenant->credits->available_credits ?? 0) }}</p>
        </div>

        <div class="glass-card p-6 rounded-xl">
            <div class="flex items-center justify-between mb-4">
                <span class="text-gray-400 text-sm">Member Since</span>
                <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-white">{{ $tenant->created_at->format('M d, Y') }}</p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="glass-card p-6 rounded-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Total Leads</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['total_leads'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="glass-card p-6 rounded-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Total Chats</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['total_chats'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="glass-card p-6 rounded-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">AI Agents</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['ai_agents'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="glass-card p-6 rounded-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-orange-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Workflows</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['workflows'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Company Information -->
        <div class="glass-card p-6 rounded-xl">
            <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Company Information
            </h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center py-3 border-b border-gray-700">
                    <span class="text-gray-400">Company Name</span>
                    <span class="text-white font-medium">{{ $tenant->name }}</span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-gray-700">
                    <span class="text-gray-400">Email</span>
                    <span class="text-white font-medium">{{ $tenant->email }}</span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-gray-700">
                    <span class="text-gray-400">Phone</span>
                    <span class="text-white font-medium">{{ $tenant->phone ?? 'Not provided' }}</span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-gray-700">
                    <span class="text-gray-400">Domain</span>
                    <span class="text-white font-medium">{{ $tenant->domain ?? 'Not configured' }}</span>
                </div>
                <div class="flex justify-between items-center py-3">
                    <span class="text-gray-400">Address</span>
                    <span class="text-white font-medium text-right max-w-xs">{{ $tenant->address ?? 'Not provided' }}</span>
                </div>
            </div>
        </div>

        <!-- Admin Users -->
        <div class="glass-card p-6 rounded-xl">
            <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Admin Users
            </h3>
            <div class="space-y-4">
                @forelse($tenant->admins ?? [] as $admin)
                    <div class="flex items-center justify-between py-3 border-b border-gray-700 last:border-0">
                        <div class="flex items-center space-x-3">
                            <div
                                class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold">
                                {{ strtoupper(substr($admin->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-white font-medium">{{ $admin->name }}</p>
                                <p class="text-gray-400 text-sm">{{ $admin->email }}</p>
                            </div>
                        </div>
                        <span
                            class="px-2 py-1 rounded text-xs {{ $admin->status === 'active' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                            {{ ucfirst($admin->status ?? 'active') }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-400 text-center py-4">No admin users found</p>
                @endforelse
            </div>
        </div>

        <!-- Credit Usage -->
        <div class="glass-card p-6 rounded-xl">
            <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Credit Information
            </h3>
            @php
                $credits = $tenant->credits;
                $totalCredits = $credits->total_credits ?? 0;
                $usedCredits = $credits->used_credits ?? 0;
                $availableCredits = $credits->available_credits ?? 0;
                $usagePercentage = $totalCredits > 0 ? ($usedCredits / $totalCredits) * 100 : 0;
            @endphp
            <div class="space-y-4">
                <div class="flex justify-between items-center py-3 border-b border-gray-700">
                    <span class="text-gray-400">Total Credits</span>
                    <span class="text-white font-bold text-xl">{{ number_format($totalCredits) }}</span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-gray-700">
                    <span class="text-gray-400">Used Credits</span>
                    <span class="text-red-400 font-medium">{{ number_format($usedCredits) }}</span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-gray-700">
                    <span class="text-gray-400">Available Credits</span>
                    <span class="text-green-400 font-bold text-xl">{{ number_format($availableCredits) }}</span>
                </div>
                <div class="pt-2">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-gray-400">Usage</span>
                        <span class="text-white">{{ number_format($usagePercentage, 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-3">
                        <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-3 rounded-full transition-all duration-500"
                            style="width: {{ min($usagePercentage, 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="glass-card p-6 rounded-xl">
            <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                Recent Payments
            </h3>
            <div class="space-y-4">
                @forelse($tenant->payments->take(5) ?? [] as $payment)
                    <div class="flex items-center justify-between py-3 border-b border-gray-700 last:border-0">
                        <div>
                            <p class="text-white font-medium">₹{{ number_format($payment->amount) }}</p>
                            <p class="text-gray-400 text-sm">{{ $payment->created_at->format('M d, Y') }}</p>
                        </div>
                        <span
                            class="px-2 py-1 rounded text-xs {{ $payment->status === 'completed' ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400' }}">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-400 text-center py-4">No payments found</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- AI System Prompt Preview -->
    <div class="mt-8 glass-card p-6 rounded-xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                AI System Prompt
            </h3>
            <a href="{{ route('superadmin.admins.edit', $tenant) }}"
                class="text-primary-400 hover:text-primary-300 text-sm flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Prompt
            </a>
        </div>
        @if($tenant->ai_system_prompt)
            <div class="bg-black/30 rounded-xl p-4 max-h-64 overflow-y-auto">
                <pre class="text-gray-300 text-sm whitespace-pre-wrap font-mono">{{ $tenant->ai_system_prompt }}</pre>
            </div>
        @else
            <div class="bg-black/20 rounded-xl p-6 text-center">
                <p class="text-gray-400">No custom AI prompt configured for this admin.</p>
                <a href="{{ route('superadmin.admins.edit', $tenant) }}"
                    class="text-primary-400 hover:underline text-sm mt-2 inline-block">Add Prompt →</a>
            </div>
        @endif
        <p class="text-gray-500 text-xs mt-2">This prompt defines how the AI chatbot talks to customers. Admin can also edit
            this in their Settings → AI Configuration.</p>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8 glass-card p-6 rounded-xl">
        <h3 class="text-lg font-semibold text-white mb-6">Quick Actions</h3>
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('superadmin.admins.chats', $tenant) }}"
                class="bg-purple-500/20 hover:bg-purple-500/30 text-purple-400 px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                View Chats
            </a>
            <button onclick="document.getElementById('addCreditsModal').classList.remove('hidden')"
                class="bg-green-500/20 hover:bg-green-500/30 text-green-400 px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Credits
            </button>
            <button onclick="document.getElementById('resetPasswordModal').classList.remove('hidden')"
                class="bg-orange-500/20 hover:bg-orange-500/30 text-orange-400 px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                Reset Password
            </button>
            <a href="{{ route('superadmin.admins.edit', $tenant) }}"
                class="bg-blue-500/20 hover:bg-blue-500/30 text-blue-400 px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Details
            </a>
            <form action="{{ route('superadmin.admins.toggle-status', $tenant) }}" method="POST" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit"
                    class="bg-{{ $tenant->status === 'active' ? 'red' : 'green' }}-500/20 hover:bg-{{ $tenant->status === 'active' ? 'red' : 'green' }}-500/30 text-{{ $tenant->status === 'active' ? 'red' : 'green' }}-400 px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                    {{ $tenant->status === 'active' ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Add Credits Modal -->
    <div id="addCreditsModal"
        class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="glass-card p-8 rounded-2xl w-full max-w-md mx-4">
            <h3 class="text-xl font-bold text-white mb-6">Add Credits to {{ $tenant->name }}</h3>
            <form action="{{ route('superadmin.admins.add-credits', $tenant) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-300 text-sm font-medium mb-2">Amount of Credits</label>
                    <input type="number" name="credits" required min="1" class="form-input w-full"
                        placeholder="Enter credits amount">
                </div>
                <div class="mb-6">
                    <label class="block text-gray-300 text-sm font-medium mb-2">Reason (Optional)</label>
                    <input type="text" name="reason" class="form-input w-full" placeholder="e.g., Monthly top-up">
                </div>
                <div class="flex space-x-4">
                    <button type="button" onclick="document.getElementById('addCreditsModal').classList.add('hidden')"
                        class="flex-1 bg-gray-700 hover:bg-gray-600 text-white py-2 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 btn-gradient py-2 rounded-lg">
                        Add Credits
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div id="resetPasswordModal"
        class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="glass-card p-8 rounded-2xl w-full max-w-md mx-4">
            <h3 class="text-xl font-bold text-white mb-2">Reset Admin Password</h3>
            <p class="text-gray-400 text-sm mb-6">Set a new password for the admin's login account</p>
            <form action="{{ route('superadmin.admins.reset-password', $tenant) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-300 text-sm font-medium mb-2">Select Admin User</label>
                    <select name="admin_id" required class="form-select w-full"
                        style="background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(148, 163, 184, 0.2); color: #fff; padding: 0.75rem 1rem; border-radius: 0.5rem;">
                        @forelse($tenant->admins ?? [] as $admin)
                            <option value="{{ $admin->id }}" style="background: #1e293b; color: #fff;">{{ $admin->name }}
                                ({{ $admin->email }})</option>
                        @empty
                            <option value="" style="background: #1e293b; color: #fff;">No admin users found</option>
                        @endforelse
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-300 text-sm font-medium mb-2">New Password</label>
                    <input type="password" name="password" required minlength="6" class="form-input w-full"
                        placeholder="Enter new password"
                        style="background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(148, 163, 184, 0.2); color: #fff;">
                </div>
                <div class="mb-6">
                    <label class="block text-gray-300 text-sm font-medium mb-2">Confirm Password</label>
                    <input type="password" name="password_confirmation" required minlength="6" class="form-input w-full"
                        placeholder="Confirm new password"
                        style="background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(148, 163, 184, 0.2); color: #fff;">
                </div>
                <div class="flex space-x-4">
                    <button type="button" onclick="document.getElementById('resetPasswordModal').classList.add('hidden')"
                        class="flex-1 bg-gray-700 hover:bg-gray-600 text-white py-2 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 btn-gradient py-2 rounded-lg">
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection