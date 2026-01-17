@extends('admin.layouts.app')

@section('title', 'Client - ' . $client->display_name)
@section('page-title', 'Client Details')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.clients.index') }}" class="p-2 rounded-lg bg-white/10 text-gray-400 hover:text-white hover:bg-white/20 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-primary-500 to-purple-500 flex items-center justify-center text-white font-bold text-xl">
                    {{ strtoupper(substr($client->name ?? $client->phone, 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">{{ $client->display_name }}</h1>
                    <p class="text-gray-400">{{ $client->phone }}</p>
                </div>
            </div>
            <a href="{{ route('admin.clients.edit', $client) }}" class="btn-gradient px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Client
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Client Profile -->
            <div class="glass rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Profile
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between py-2 border-b border-white/10">
                        <span class="text-gray-400">Name</span>
                        <span class="text-white">{{ $client->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-white/10">
                        <span class="text-gray-400">Business</span>
                        <span class="text-white">{{ $client->business_name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-white/10">
                        <span class="text-gray-400">GST Number</span>
                        <span class="text-white">{{ $client->gst_number ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-white/10">
                        <span class="text-gray-400">City</span>
                        <span class="text-white">{{ $client->city ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-white/10">
                        <span class="text-gray-400">State</span>
                        <span class="text-white">{{ $client->state ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-white/10">
                        <span class="text-gray-400">Phone</span>
                        <span class="text-white">{{ $client->phone }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-white/10">
                        <span class="text-gray-400">Email</span>
                        <span class="text-white">{{ $client->email ?? '-' }}</span>
                    </div>
                    <div class="py-2">
                        <span class="text-gray-400 block mb-1">Address</span>
                        <span class="text-white text-sm">{{ $client->address ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Global Fields (from workflow) -->
            <div class="glass rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Collected Data
                </h3>
                <div class="space-y-3">
                    @if($client->global_fields && count($client->global_fields) > 0)
                        @foreach($client->global_fields as $key => $value)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <span class="text-gray-400 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                                <span class="text-white">{{ is_array($value) ? json_encode($value) : $value }}</span>
                            </div>
                        @endforeach
                    @else
                        <p class="text-gray-500 text-center py-4">No collected data</p>
                    @endif
                </div>
            </div>

            <!-- Notes -->
            <div class="glass rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Notes
                </h3>
                <div class="text-gray-300">
                    {{ $client->notes ?? 'No notes yet' }}
                </div>
                <p class="text-gray-500 text-xs mt-4">
                    Client since: {{ $client->created_at->format('M d, Y') }}
                </p>
            </div>
        </div>

        <!-- Lead History -->
        <div class="glass rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Lead History
                </h3>
            </div>
            <table class="w-full">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Lead ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Stage</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Created</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($leads as $lead)
                        <tr class="hover:bg-white/5">
                            <td class="px-6 py-3 text-white">#{{ $lead->id }}</td>
                            <td class="px-6 py-3">
                                @if($lead->leadStatus)
                                    <span class="px-2 py-1 rounded-lg text-xs" style="background-color: {{ $lead->leadStatus->color }}20; color: {{ $lead->leadStatus->color }}">
                                        {{ $lead->leadStatus->name }}
                                    </span>
                                @else
                                    <span class="px-2 py-1 rounded-lg text-xs bg-gray-500/20 text-gray-400">{{ ucfirst($lead->status) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-gray-300">{{ ucfirst(str_replace('_', ' ', $lead->stage)) }}</td>
                            <td class="px-6 py-3 text-gray-400 text-sm">{{ $lead->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-3 text-center">
                                <a href="{{ route('admin.leads.show', $lead) }}" class="text-primary-400 hover:text-primary-300 text-sm">
                                    View →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400">No leads found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
