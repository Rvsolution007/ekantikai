@extends('admin.layouts.app')

@section('title', 'Clients')
@section('page-title', 'Clients')

@section('content')
    <div class="space-y-6">
        <!-- Header & Stats -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Clients</h1>
                <p class="text-gray-400 mt-1">Manage your confirmed clients</p>
            </div>
            <div class="flex gap-4">
                <div class="glass-light rounded-xl px-4 py-2">
                    <span class="text-gray-400 text-sm">Total:</span>
                    <span class="text-white font-bold ml-1">{{ $stats['total'] }}</span>
                </div>
                <div class="glass-light rounded-xl px-4 py-2">
                    <span class="text-gray-400 text-sm">This Month:</span>
                    <span class="text-green-400 font-bold ml-1">+{{ $stats['this_month'] }}</span>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="glass rounded-xl p-4">
            <form action="{{ route('admin.clients.index') }}" method="GET" class="flex gap-4">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search clients by name, business, phone, city..."
                    class="flex-1 bg-dark-300 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none">
                <button type="submit" class="btn-gradient px-6 py-2 rounded-lg">Search</button>
                @if(request('search'))
                    <a href="{{ route('admin.clients.index') }}" class="px-4 py-2 text-gray-400 hover:text-white">Clear</a>
                @endif
            </form>
        </div>

        <!-- Clients List -->
        <div class="glass rounded-2xl overflow-hidden">
            <table class="w-full">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Client</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Business</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">City</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Phone</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Since</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($clients as $client)
                        <tr class="table-row hover:bg-white/5">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-purple-500 flex items-center justify-center text-white font-bold">
                                        {{ strtoupper(substr($client->name ?? $client->phone, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-white font-medium">{{ $client->name ?? 'N/A' }}</p>
                                        <p class="text-gray-400 text-sm">{{ $client->email ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-300">{{ $client->business_name ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-300">{{ $client->city ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-300">{{ $client->phone }}</td>
                            <td class="px-6 py-4 text-gray-400 text-sm">{{ $client->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.clients.show', $client) }}"
                                        class="p-2 text-gray-400 hover:text-white hover:bg-white/10 rounded-lg transition-colors"
                                        title="View">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.clients.edit', $client) }}"
                                        class="p-2 text-gray-400 hover:text-primary-400 hover:bg-primary-500/10 rounded-lg transition-colors"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-white/5 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <p class="text-gray-400">No clients yet</p>
                                <p class="text-gray-500 text-sm mt-1">Clients are created when leads are confirmed</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($clients->hasPages())
                <div class="p-4 border-t border-white/5">
                    {{ $clients->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection