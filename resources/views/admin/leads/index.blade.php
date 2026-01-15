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

        <!-- Filters -->
        <div class="glass rounded-xl p-4">
            <form action="{{ route('admin.leads.index') }}" method="GET" class="flex flex-wrap gap-4 items-center">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or number..."
                    class="input-dark flex-1 min-w-[200px] px-4 py-2 rounded-xl text-white placeholder-gray-500">
                <select name="stage" class="input-dark px-4 py-2 rounded-xl text-white">
                    <option value="">All Stages</option>
                    <option value="new" {{ request('stage') === 'new' ? 'selected' : '' }}>New</option>
                    <option value="qualified" {{ request('stage') === 'qualified' ? 'selected' : '' }}>Qualified</option>
                    <option value="confirmed" {{ request('stage') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="lost" {{ request('stage') === 'lost' ? 'selected' : '' }}>Lost</option>
                </select>
                <select name="quality" class="input-dark px-4 py-2 rounded-xl text-white">
                    <option value="">All Qualities</option>
                    <option value="hot" {{ request('quality') === 'hot' ? 'selected' : '' }}>Hot</option>
                    <option value="warm" {{ request('quality') === 'warm' ? 'selected' : '' }}>Warm</option>
                    <option value="cold" {{ request('quality') === 'cold' ? 'selected' : '' }}>Cold</option>
                </select>
                <button type="submit" class="btn-primary px-6 py-2 rounded-xl text-white font-medium">Filter</button>
                <a href="{{ route('admin.leads.index') }}"
                    class="px-4 py-2 text-gray-400 hover:text-white transition-colors">Reset</a>
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
                                <span class="px-3 py-1 text-xs font-medium rounded-lg
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