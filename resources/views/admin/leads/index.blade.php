@extends('admin.layouts.app')

@section('title', 'Leads')
@section('page-title', 'Lead Management')

@section('content')
    <div class="space-y-6">
        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="stat-card glass rounded-xl p-4">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-white">{{ $stats['total'] ?? 0 }}</p>
                        <p class="text-gray-400 text-sm">Total</p>
                    </div>
                </div>
            </div>
            <div class="stat-card glass rounded-xl p-4">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-green-400">{{ $stats['new'] ?? 0 }}</p>
                        <p class="text-gray-400 text-sm">New</p>
                    </div>
                </div>
            </div>
            <div class="stat-card glass rounded-xl p-4">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 rounded-lg bg-gradient-to-br from-yellow-500 to-orange-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-yellow-400">{{ $stats['qualified'] ?? 0 }}</p>
                        <p class="text-gray-400 text-sm">Qualified</p>
                    </div>
                </div>
            </div>
            <div class="stat-card glass rounded-xl p-4">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-purple-400">{{ $stats['confirmed'] ?? 0 }}</p>
                        <p class="text-gray-400 text-sm">Confirmed</p>
                    </div>
                </div>
            </div>
            <div class="stat-card glass rounded-xl p-4">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 rounded-lg bg-gradient-to-br from-red-500 to-rose-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-red-400">{{ $stats['lost'] ?? 0 }}</p>
                        <p class="text-gray-400 text-sm">Lost</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Toggle & Actions -->
        <div class="flex justify-between items-center">
            <div class="flex gap-2">
                <a href="{{ route('admin.leads.index') }}"
                    class="px-4 py-2 bg-primary-500/20 text-primary-400 rounded-xl flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    List
                </a>
                <a href="{{ route('admin.lead-status.kanban') }}"
                    class="px-4 py-2 bg-gray-500/20 text-gray-400 hover:bg-purple-500/20 hover:text-purple-400 rounded-xl flex items-center gap-2 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                    </svg>
                    Kanban
                </a>
            </div>
            <a href="{{ route('admin.lead-status.index') }}"
                class="px-4 py-2 text-gray-400 hover:text-white transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Manage Statuses
            </a>
        </div>

        <!-- Search & Filter Bar -->
        <div class="glass rounded-xl p-4 relative z-20">
            <form action="{{ route('admin.leads.index') }}" method="GET" id="filterForm">
                <div class="flex items-center gap-4">
                    <!-- Search Input -->
                    <div class="flex-1">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search by name or number..."
                            class="input-dark w-full px-4 py-3 rounded-xl text-white placeholder-gray-500"
                            onkeypress="if(event.key==='Enter') document.getElementById('filterForm').submit()">
                    </div>

                    <!-- Filter Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button type="button" @click="open = !open"
                            class="flex items-center gap-2 px-5 py-3 bg-gray-700/50 hover:bg-gray-600/50 border border-gray-600 rounded-xl text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filter
                            @if(request('stage') || request('quality') || request('status'))
                                <span class="w-2 h-2 bg-primary-500 rounded-full"></span>
                            @endif
                            <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown Panel -->
                        <div x-show="open" @click.away="open = false" x-transition
                            class="absolute right-0 mt-2 w-72 bg-dark-100 border border-gray-700 rounded-xl shadow-2xl p-4 space-y-4" style="z-index: 9999;">

                            <!-- Stage Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">Stage</label>
                                <select name="stage" class="input-dark w-full px-4 py-2.5 rounded-lg text-white">
                                    <option value="">All Stages</option>
                                    <option value="New Lead" {{ request('stage') === 'New Lead' ? 'selected' : '' }}>New Lead
                                    </option>
                                    <option value="Qualified" {{ request('stage') === 'Qualified' ? 'selected' : '' }}>
                                        Qualified</option>
                                    <option value="Confirm" {{ request('stage') === 'Confirm' ? 'selected' : '' }}>Confirmed
                                    </option>
                                    <option value="Lose" {{ request('stage') === 'Lose' ? 'selected' : '' }}>Lost</option>
                                </select>
                            </div>

                            <!-- Quality Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">Lead Quality</label>
                                <select name="quality" class="input-dark w-full px-4 py-2.5 rounded-lg text-white">
                                    <option value="">All Qualities</option>
                                    <option value="hot" {{ request('quality') === 'hot' ? 'selected' : '' }}>🔥 Hot</option>
                                    <option value="warm" {{ request('quality') === 'warm' ? 'selected' : '' }}>🌡️ Warm
                                    </option>
                                    <option value="cold" {{ request('quality') === 'cold' ? 'selected' : '' }}>❄️ Cold
                                    </option>
                                </select>
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">Lead Status</label>
                                <select name="status" class="input-dark w-full px-4 py-2.5 rounded-lg text-white">
                                    <option value="">All Status</option>
                                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed
                                    </option>
                                </select>
                            </div>

                            <!-- Filter Actions -->
                            <div class="flex gap-3 pt-2 border-t border-gray-700">
                                <button type="submit" @click="open = false"
                                    class="flex-1 btn-primary px-4 py-2.5 rounded-lg text-white font-medium text-sm">
                                    Apply Filters
                                </button>
                                <a href="{{ route('admin.leads.index') }}"
                                    class="px-4 py-2.5 bg-gray-700/50 hover:bg-gray-600/50 rounded-lg text-gray-300 text-sm font-medium transition-colors">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Filters Display -->
                @if(request('stage') || request('quality') || request('status') || request('search'))
                    <div class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-white/5">
                        <span class="text-sm text-gray-500">Active filters:</span>
                        @if(request('search'))
                            <span class="px-2 py-1 bg-primary-500/20 text-primary-400 text-xs rounded-lg flex items-center gap-1">
                                Search: "{{ request('search') }}"
                                <a href="{{ route('admin.leads.index', array_merge(request()->except('search'))) }}"
                                    class="hover:text-white">×</a>
                            </span>
                        @endif
                        @if(request('stage'))
                            <span class="px-2 py-1 bg-green-500/20 text-green-400 text-xs rounded-lg flex items-center gap-1">
                                Stage: {{ request('stage') }}
                                <a href="{{ route('admin.leads.index', array_merge(request()->except('stage'))) }}"
                                    class="hover:text-white">×</a>
                            </span>
                        @endif
                        @if(request('quality'))
                            <span class="px-2 py-1 bg-orange-500/20 text-orange-400 text-xs rounded-lg flex items-center gap-1">
                                Quality: {{ ucfirst(request('quality')) }}
                                <a href="{{ route('admin.leads.index', array_merge(request()->except('quality'))) }}"
                                    class="hover:text-white">×</a>
                            </span>
                        @endif
                        @if(request('status'))
                            <span class="px-2 py-1 bg-purple-500/20 text-purple-400 text-xs rounded-lg flex items-center gap-1">
                                Status: {{ ucfirst(request('status')) }}
                                <a href="{{ route('admin.leads.index', array_merge(request()->except('status'))) }}"
                                    class="hover:text-white">×</a>
                            </span>
                        @endif
                    </div>
                @endif
            </form>
        </div>

        <!-- Leads Table -->
        <div class="glass rounded-2xl overflow-hidden">
            <table class="w-full">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Contact</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Stage</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Quality</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Purpose</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Assigned To</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Created</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($leads ?? [] as $lead)
                        <tr class="table-row">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center">
                                        <span class="text-white font-medium">{{ substr($lead->contact_name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-white font-medium">{{ $lead->contact_name }}</p>
                                        <p class="text-gray-400 text-sm">{{ $lead->contact_phone }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs font-medium rounded-lg
                                                            {{ $lead->stage === 'New Lead' ? 'bg-green-500/20 text-green-400' : '' }}
                                                            {{ $lead->stage === 'Qualified' ? 'bg-yellow-500/20 text-yellow-400' : '' }}
                                                            {{ $lead->stage === 'Confirm' ? 'bg-purple-500/20 text-purple-400' : '' }}
                                                            {{ $lead->stage === 'Lose' ? 'bg-red-500/20 text-red-400' : '' }}">
                                    {{ $lead->stage }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-3 py-1 text-xs font-medium rounded-lg
                                                            {{ $lead->lead_quality === 'hot' ? 'bg-red-500/20 text-red-400' : '' }}
                                                            {{ $lead->lead_quality === 'warm' ? 'bg-orange-500/20 text-orange-400' : '' }}
                                                            {{ $lead->lead_quality === 'cold' ? 'bg-blue-500/20 text-blue-400' : '' }}">
                                    {{ ucfirst($lead->lead_quality ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-300">
                                @php
                                    $products = $lead->collected_data['products'] ?? [];
                                    $productCount = count($products);
                                @endphp
                                @if($productCount > 0)
                                    <span class="text-primary-400">{{ $productCount }} product(s)</span>
                                @else
                                    {{ $lead->purpose_of_purchase ?? '-' }}
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-300">{{ $lead->assignedAdmin->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-400">{{ $lead->created_at->diffForHumans() }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('admin.leads.show', $lead) }}"
                                        class="p-2 text-gray-400 hover:text-white transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857" />
                                </svg>
                                <p class="text-lg font-medium">No leads found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if(isset($leads) && $leads->hasPages())
                <div class="p-4 border-t border-white/5">
                    {{ $leads->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection