@extends('superadmin.layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">Project Structure</h1>
                <p class="text-gray-400 mt-1">SuperAdmin sidebar modules</p>
            </div>
        </div>

        <!-- Sidebar Items List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($sidebarItems as $item)
                <a href="{{ route('superadmin.project-structure.show', $item['slug']) }}"
                    class="glass-card p-6 rounded-2xl hover:border-purple-500/50 transition-all group">
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
            @endforeach
        </div>
    </div>
@endsection