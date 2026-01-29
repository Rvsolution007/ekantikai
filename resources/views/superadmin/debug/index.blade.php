@extends('superadmin.layouts.app')

@section('title', 'Debug Dashboard')
@section('page-title', 'Debug Dashboard')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="glass-card rounded-2xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-white">System Debug</h2>
                    <p class="text-gray-400 mt-1">Scan and diagnose admin configurations</p>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-400">{{ count($adminBadges) }} Admins</span>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="flex items-center space-x-6 text-sm">
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-green-500"></span>
                <span class="text-gray-400">CONNECTED - All checks passed</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                <span class="text-gray-400">NOT CONNECTED - Issues found</span>
            </div>
        </div>

        <!-- Admin List -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/10">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Admin
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Status
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Quick
                            Checks</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($adminBadges as $adminId => $data)
                        <tr class="table-row hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center">
                                        <span
                                            class="text-white font-semibold text-sm">{{ strtoupper(substr($data['admin']->name, 0, 2)) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-white font-medium">{{ $data['admin']->name }}</p>
                                        <p class="text-gray-500 text-sm">ID: {{ $data['admin']->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($data['badge'] === 'CONNECTED')
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400 border border-green-500/30">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        CONNECTED
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-500/20 text-red-400 border border-red-500/30">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        NOT CONNECTED
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3 text-xs">
                                    <span
                                        class="flex items-center {{ $data['quick_checks']['start_node'] ? 'text-green-400' : 'text-red-400' }}">
                                        @if($data['quick_checks']['start_node'])
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                        Start
                                    </span>
                                    <span
                                        class="flex items-center {{ $data['quick_checks']['connections'] ? 'text-green-400' : 'text-red-400' }}">
                                        @if($data['quick_checks']['connections'])
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                        Flow
                                    </span>
                                    <span
                                        class="flex items-center {{ $data['quick_checks']['catalogue'] ? 'text-green-400' : 'text-red-400' }}">
                                        @if($data['quick_checks']['catalogue'])
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                        Catalogue
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('superadmin.debug.show', $data['admin']->id) }}"
                                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-600 to-purple-600 hover:from-primary-500 hover:to-purple-500 text-white text-sm font-medium rounded-xl transition-all hover:shadow-lg hover:shadow-primary-500/25">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                    </svg>
                                    Debug
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection