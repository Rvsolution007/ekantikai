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

        <!-- Empty State - User will configure later -->
        <div class="glass-card p-12 rounded-2xl text-center">
            <div
                class="w-16 h-16 mx-auto rounded-xl bg-gradient-to-br from-purple-500/20 to-pink-500/20 flex items-center justify-center mb-4">
                @include('superadmin.project-structure.partials.icon', ['icon' => $subItem['icon']])
            </div>
            <h3 class="text-xl font-semibold text-white mb-2">{{ $subItem['name'] }}</h3>
            <p class="text-gray-400">Configuration coming soon</p>
        </div>
    </div>
@endsection