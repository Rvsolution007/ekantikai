@extends('superadmin.layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Header with Back Button -->
        <div class="flex items-center space-x-4">
            <a href="{{ route('superadmin.project-structure.show', $currentModule['slug']) }}"
                class="p-2 rounded-xl bg-white/5 hover:bg-white/10 transition-colors">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $subItem['name'] }}</h1>
                <p class="text-gray-400 mt-1">Project Structure > {{ $currentModule['name'] }} > {{ $subItem['name'] }}</p>
            </div>
        </div>

        <!-- Workflow Sub Items -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($workflowItems as $item)
                @php
                    $routeName = match ($item['slug']) {
                        'product-questions' => 'superadmin.project-structure.product-questions',
                        'global-questions' => 'superadmin.project-structure.global-questions',
                        'flowchart' => 'superadmin.project-structure.flowchart',
                        default => null
                    };
                    $isActive = $routeName !== null;
                @endphp

                @if($isActive)
                    <a href="{{ route($routeName) }}"
                        class="glass-card p-6 rounded-2xl hover:border-purple-500/50 transition-all group cursor-pointer">
                        <div class="flex items-center space-x-4">
                            <div
                                class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500/20 to-pink-500/20 flex items-center justify-center group-hover:from-purple-500/30 group-hover:to-pink-500/30 transition-all">
                                @include('superadmin.project-structure.partials.icon', ['icon' => $item['icon']])
                            </div>
                            <div>
                                <h3 class="text-white font-semibold">{{ $item['name'] }}</h3>
                            </div>
                        </div>
                    </a>
                @else
                    <div class="glass-card p-6 rounded-2xl opacity-50 cursor-not-allowed">
                        <div class="flex items-center space-x-4">
                            <div
                                class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500/20 to-pink-500/20 flex items-center justify-center">
                                @include('superadmin.project-structure.partials.icon', ['icon' => $item['icon']])
                            </div>
                            <div>
                                <h3 class="text-white font-semibold">{{ $item['name'] }}</h3>
                                <p class="text-xs text-gray-500">Coming soon</p>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endsection