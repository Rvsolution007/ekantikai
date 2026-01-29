@extends('superadmin.layouts.app')

@section('title', 'Debug: ' . $admin->name)
@section('page-title', 'Debug: ' . $admin->name)

@section('content')
    <div class="space-y-6">
        <!-- Back Button & Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('superadmin.debug.index') }}"
                    class="p-2 rounded-xl bg-white/5 hover:bg-white/10 transition-colors text-gray-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-white">{{ $admin->name }}</h2>
                    <p class="text-gray-400 text-sm">Scanned: {{ $result['scanned_at'] }}</p>
                </div>
            </div>

            <!-- Badge -->
            @if($result['badge'] === 'CONNECTED')
                <div class="flex items-center px-6 py-3 rounded-2xl bg-green-500/20 border border-green-500/30">
                    <svg class="w-6 h-6 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="text-green-400 font-bold text-lg">CONNECTED</span>
                </div>
            @else
                <div class="flex items-center px-6 py-3 rounded-2xl bg-red-500/20 border border-red-500/30">
                    <svg class="w-6 h-6 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="text-red-400 font-bold text-lg">NOT CONNECTED</span>
                </div>
            @endif
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="glass-card rounded-2xl p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Total Checks</p>
                        <p class="text-2xl font-bold text-white mt-1">{{ $result['summary']['total_checks'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-primary-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="glass-card rounded-2xl p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Passed</p>
                        <p class="text-2xl font-bold text-green-400 mt-1">{{ $result['summary']['passed'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="glass-card rounded-2xl p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Failed</p>
                        <p class="text-2xl font-bold text-red-400 mt-1">{{ $result['summary']['failed'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-red-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="glass-card rounded-2xl p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Warnings</p>
                        <p class="text-2xl font-bold text-yellow-400 mt-1">{{ $result['summary']['warnings'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-yellow-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Failed Checks (Errors) -->
        @if(count($result['checks_failed']) > 0)
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-red-400 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                    Failed Checks ({{ count($result['checks_failed']) }})
                </h3>
                <div class="space-y-3">
                    @foreach($result['checks_failed'] as $error)
                        <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <span
                                            class="px-2 py-0.5 text-xs font-medium rounded bg-red-500/30 text-red-300 uppercase">{{ $error['severity'] }}</span>
                                        <h4 class="text-white font-medium">{{ $error['name'] }}</h4>
                                    </div>
                                    <p class="text-gray-400 text-sm mt-2">{{ $error['details'] }}</p>
                                    @if(isset($error['fix']))
                                        <div class="mt-3 flex items-center text-sm">
                                            <span class="text-gray-500 mr-2">Fix:</span>
                                            <span class="text-primary-400">{{ $error['fix'] }}</span>
                                        </div>
                                    @endif
                                </div>
                                <span class="text-xs text-gray-500">{{ $error['id'] ?? '' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Warnings -->
        @if(count($result['warnings']) > 0)
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-yellow-400 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Warnings ({{ count($result['warnings']) }})
                </h3>
                <div class="space-y-3">
                    @foreach($result['warnings'] as $warning)
                        <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-xl p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="text-white font-medium">{{ $warning['name'] }}</h4>
                                    <p class="text-gray-400 text-sm mt-1">{{ $warning['details'] }}</p>
                                    @if(isset($warning['fix']))
                                        <div class="mt-2 flex items-center text-sm">
                                            <span class="text-gray-500 mr-2">Suggestion:</span>
                                            <span class="text-yellow-400">{{ $warning['fix'] }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Passed Checks -->
        @if(count($result['checks_passed']) > 0)
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-green-400 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                    Passed Checks ({{ count($result['checks_passed']) }})
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($result['checks_passed'] as $check)
                        <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-4">
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-white font-medium">{{ $check['name'] }}</h4>
                                    <p class="text-gray-400 text-sm truncate" title="{{ $check['details'] }}">
                                        {{ $check['details'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Inventory -->
        <div class="glass-card rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                </svg>
                Inventory
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                @foreach($result['inventory'] as $key => $value)
                    <div class="bg-white/5 rounded-xl p-4 text-center">
                        <p class="text-2xl font-bold text-white">{{ $value }}</p>
                        <p class="text-gray-400 text-xs mt-1">{{ ucwords(str_replace('_', ' ', $key)) }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Rescan Button -->
        <div class="flex justify-center">
            <button onclick="window.location.reload()"
                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-primary-600 to-purple-600 hover:from-primary-500 hover:to-purple-500 text-white font-medium rounded-xl transition-all hover:shadow-lg hover:shadow-primary-500/25">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Rescan Now
            </button>
        </div>
    </div>
@endsection