@extends('superadmin.layouts.app')

@section('title', 'AI Configuration')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-white">AI Configuration</h1>
                <p class="text-gray-400">Global AI provider and model settings</p>
            </div>
            <a href="{{ route('superadmin.ai-config.dashboard') }}" 
               class="px-4 py-2 bg-purple-500/20 text-purple-400 rounded-xl hover:bg-purple-500/30 transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Usage Dashboard
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-500/20 text-green-400 px-4 py-3 rounded-xl">{{ session('success') }}</div>
        @endif

        <form action="{{ route('superadmin.ai-config.update') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Provider Selection -->
            <div class="glass rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">AI Provider</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach(['google' => ['name' => 'Google Gemini', 'icon' => '🌟', 'desc' => 'Fast, cost-effective'], 'openai' => ['name' => 'OpenAI', 'icon' => '🤖', 'desc' => 'GPT-4, most capable'], 'deepseek' => ['name' => 'DeepSeek', 'icon' => '🔍', 'desc' => 'Budget-friendly']] as $key => $provider)
                        <label class="relative cursor-pointer">
                            <input type="radio" name="provider" value="{{ $key }}" class="peer sr-only"
                                   {{ $currentProvider === $key ? 'checked' : '' }}>
                            <div class="p-4 rounded-xl border-2 border-gray-700 peer-checked:border-primary-500 peer-checked:bg-primary-500/10 hover:border-gray-600 transition-colors">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="text-2xl">{{ $provider['icon'] }}</span>
                                    <span class="text-white font-medium">{{ $provider['name'] }}</span>
                                </div>
                                <p class="text-gray-400 text-sm">{{ $provider['desc'] }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Model Selection -->
            <div class="glass rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Model</h2>
                <select name="model" class="w-full input-dark px-4 py-3 rounded-xl text-white">
                    @foreach($models as $provider => $providerModels)
                        <optgroup label="{{ ucfirst($provider) }}">
                            @foreach($providerModels as $modelKey => $modelName)
                                <option value="{{ $modelKey }}" {{ $currentModel === $modelKey ? 'selected' : '' }}>
                                    {{ $modelName }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            <!-- API Keys -->
            <div class="glass rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">API Keys</h2>
                <p class="text-gray-400 text-sm mb-4">Leave blank to keep existing keys. Keys are stored encrypted.</p>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Google Gemini API Key</label>
                        <input type="password" name="gemini_api_key" 
                               class="w-full input-dark px-4 py-3 rounded-xl text-white"
                               placeholder="••••••••••••••••">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">OpenAI API Key</label>
                        <input type="password" name="openai_api_key" 
                               class="w-full input-dark px-4 py-3 rounded-xl text-white"
                               placeholder="••••••••••••••••">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">DeepSeek API Key</label>
                        <input type="password" name="deepseek_api_key" 
                               class="w-full input-dark px-4 py-3 rounded-xl text-white"
                               placeholder="••••••••••••••••">
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end">
                <button type="submit" 
                        class="px-6 py-3 bg-gradient-to-r from-primary-500 to-purple-600 text-white rounded-xl hover:opacity-90 transition-opacity">
                    Save Configuration
                </button>
            </div>
        </form>
    </div>
@endsection
