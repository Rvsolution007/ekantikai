@extends('admin.layouts.app')

@section('title', 'Workflow')

@section('content')
    <div class="p-4 lg:p-6">
        <!-- Header -->
        <div class="glass rounded-2xl p-6 mb-8">
            <div class="flex items-center gap-4">
                <div
                    class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-purple-500 flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Workflow</h1>
                    <p class="text-gray-400 mt-1">Configure your chatbot's conversation flow and questions</p>
                </div>
            </div>
        </div>

        <!-- 3 Section Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Product Question -->
            <a href="{{ route('admin.workflow.fields.index') }}"
                class="glass rounded-2xl p-6 hover:bg-white/10 transition-all group cursor-pointer">
                <div class="flex flex-col items-center text-center">
                    <div
                        class="w-20 h-20 rounded-2xl bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-lg shadow-orange-500/25">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Product Question</h3>
                    <p class="text-gray-400 text-sm">Define product-related fields like Category, Model, Size, Finish,
                        Quantity</p>
                    <div class="mt-4 flex items-center gap-2 text-primary-400">
                        <span class="text-sm font-medium">Manage Fields</span>
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </div>
            </a>

            <!-- Global Questions -->
            <a href="{{ route('admin.workflow.global.index') }}"
                class="glass rounded-2xl p-6 hover:bg-white/10 transition-all group cursor-pointer">
                <div class="flex flex-col items-center text-center">
                    <div
                        class="w-20 h-20 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-500 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-lg shadow-cyan-500/25">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Global Questions</h3>
                    <p class="text-gray-400 text-sm">One-time questions like City, Purpose of Purchase, Contact Info</p>
                    <div class="mt-4 flex items-center gap-2 text-cyan-400">
                        <span class="text-sm font-medium">Manage Questions</span>
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </div>
            </a>

            <!-- Flowchart Builder -->
            <a href="{{ route('admin.workflow.flowchart.index') }}"
                class="glass rounded-2xl p-6 hover:bg-white/10 transition-all group cursor-pointer">
                <div class="flex flex-col items-center text-center">
                    <div
                        class="w-20 h-20 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-lg shadow-purple-500/25">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Flowchart Builder</h3>
                    <p class="text-gray-400 text-sm">Design your conversation flow visually with drag-and-drop nodes</p>
                    <div class="mt-4 flex items-center gap-2 text-purple-400">
                        <span class="text-sm font-medium">Open Builder</span>
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </div>
            </a>
        </div>

        <!-- Quick Info -->
        <div class="mt-8 glass-light rounded-2xl p-6">
            <h3 class="text-white font-semibold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                How Workflow Works
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex items-start gap-3">
                    <span
                        class="w-8 h-8 rounded-lg bg-orange-500/20 text-orange-400 flex items-center justify-center font-bold">1</span>
                    <div>
                        <p class="text-white font-medium">Define Product Fields</p>
                        <p class="text-gray-400 text-sm">Set up product-related questions like Category, Model, Size</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span
                        class="w-8 h-8 rounded-lg bg-cyan-500/20 text-cyan-400 flex items-center justify-center font-bold">2</span>
                    <div>
                        <p class="text-white font-medium">Add Global Questions</p>
                        <p class="text-gray-400 text-sm">Configure one-time questions like City, Purpose</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span
                        class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold">3</span>
                    <div>
                        <p class="text-white font-medium">Build Flowchart</p>
                        <p class="text-gray-400 text-sm">Design conversation flow with visual builder</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection