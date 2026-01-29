@extends('superadmin.layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Header with Back Button -->
        <div class="flex items-center space-x-4">
            <a href="{{ route('superadmin.project-structure.index') }}"
                class="p-2 rounded-xl bg-white/5 hover:bg-white/10 transition-colors">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $currentModule['name'] }}</h1>
                <p class="text-gray-400 mt-1">Project Structure > {{ $currentModule['name'] }}</p>
            </div>
        </div>

        @if(count($subItems) > 0)
            <!-- Sub Items List -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($subItems as $item)
                    <div class="glass-card p-6 rounded-2xl cursor-not-allowed opacity-75">
                        <div class="flex items-center space-x-4">
                            <div
                                class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500/20 to-pink-500/20 flex items-center justify-center">
                                @include('superadmin.project-structure.partials.icon', ['icon' => $item['icon']])
                            </div>
                            <div>
                                <h3 class="text-white font-semibold">{{ $item['name'] }}</h3>
                                @if(isset($item['parent']))
                                    <p class="text-xs text-gray-500">Sub-item of {{ $item['parent'] }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="glass-card p-12 rounded-2xl text-center">
                <div
                    class="w-16 h-16 mx-auto rounded-xl bg-gradient-to-br from-purple-500/20 to-pink-500/20 flex items-center justify-center mb-4">
                    @include('superadmin.project-structure.partials.icon', ['icon' => $currentModule['icon']])
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">{{ $currentModule['name'] }}</h3>
                <p class="text-gray-400">No sub-items configured yet</p>
            </div>
        @endif
    </div>
@endsection