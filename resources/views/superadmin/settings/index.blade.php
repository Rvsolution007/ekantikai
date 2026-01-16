@extends('superadmin.layouts.app')

@section('title', 'Settings')
@section('page-title', 'Platform Settings')

@section('content')
    <div class="space-y-6">
        <!-- General Settings -->
        <div class="glass rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <h3 class="text-lg font-semibold text-white">General Settings</h3>
                <p class="text-gray-400 text-sm mt-1">Configure platform-wide settings</p>
            </div>
            <div class="p-6">
                <form action="#" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Platform Name</label>
                            <input type="text" name="platform_name" value="ChatBot SaaS"
                                class="input-dark w-full px-4 py-3 rounded-xl text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Support Email</label>
                            <input type="email" name="support_email" value="support@chatbot.com"
                                class="input-dark w-full px-4 py-3 rounded-xl text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Default Timezone</label>
                        <select name="timezone" class="input-dark w-full px-4 py-3 rounded-xl text-white">
                            <option value="Asia/Kolkata" selected>Asia/Kolkata (IST)</option>
                            <option value="UTC">UTC</option>
                            <option value="America/New_York">America/New_York (EST)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary px-6 py-3 rounded-xl text-white font-medium">
                        Save Settings
                    </button>
                </form>
            </div>
        </div>

        <!-- Subscription Plans -->
        <div class="glass rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <h3 class="text-lg font-semibold text-white">Subscription Plans</h3>
                <p class="text-gray-400 text-sm mt-1">Configure pricing and limits for each plan</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Free Plan -->
                    <div class="glass-light rounded-xl p-5 border border-gray-600">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-white font-semibold">Free</h4>
                            <span class="px-2 py-1 text-xs rounded-lg bg-gray-500/20 text-gray-400">Trial</span>
                        </div>
                        <p class="text-3xl font-bold text-white mb-4">₹0<span class="text-sm text-gray-400">/mo</span></p>
                        <ul class="space-y-2 text-sm text-gray-400">
                            <li>• 100 messages/month</li>
                            <li>• 1 AI Agent</li>
                            <li>• 2 Workflows</li>
                            <li>• 14 day trial</li>
                        </ul>
                    </div>

                    <!-- Basic Plan -->
                    <div class="glass-light rounded-xl p-5 border border-blue-500/30">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-white font-semibold">Basic</h4>
                            <span class="px-2 py-1 text-xs rounded-lg bg-blue-500/20 text-blue-400">Popular</span>
                        </div>
                        <p class="text-3xl font-bold text-white mb-4">₹999<span class="text-sm text-gray-400">/mo</span></p>
                        <ul class="space-y-2 text-sm text-gray-400">
                            <li>• 1,000 messages/month</li>
                            <li>• 3 AI Agents</li>
                            <li>• 10 Workflows</li>
                            <li>• Email support</li>
                        </ul>
                    </div>

                    <!-- Pro Plan -->
                    <div class="glass-light rounded-xl p-5 border border-purple-500/30 relative">
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span
                                class="px-3 py-1 text-xs rounded-full bg-gradient-to-r from-purple-500 to-pink-500 text-white">Recommended</span>
                        </div>
                        <div class="flex items-center justify-between mb-4 mt-2">
                            <h4 class="text-white font-semibold">Pro</h4>
                        </div>
                        <p class="text-3xl font-bold text-white mb-4">₹2,999<span class="text-sm text-gray-400">/mo</span>
                        </p>
                        <ul class="space-y-2 text-sm text-gray-400">
                            <li>• 10,000 messages/month</li>
                            <li>• 10 AI Agents</li>
                            <li>• 50 Workflows</li>
                            <li>• Priority support</li>
                        </ul>
                    </div>

                    <!-- Enterprise Plan -->
                    <div class="glass-light rounded-xl p-5 border border-yellow-500/30">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-white font-semibold">Enterprise</h4>
                            <span class="px-2 py-1 text-xs rounded-lg bg-yellow-500/20 text-yellow-400">Custom</span>
                        </div>
                        <p class="text-3xl font-bold text-white mb-4">Custom</p>
                        <ul class="space-y-2 text-sm text-gray-400">
                            <li>• Unlimited messages</li>
                            <li>• Unlimited AI Agents</li>
                            <li>• Unlimited Workflows</li>
                            <li>• Dedicated support</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Credit Rates -->
        <div class="glass rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <h3 class="text-lg font-semibold text-white">Credit Rates</h3>
                <p class="text-gray-400 text-sm mt-1">Configure credit consumption rates</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Credit per Message</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">₹</span>
                            <input type="number" step="0.01" name="credit_per_message" value="0.10"
                                class="input-dark w-full pl-8 pr-4 py-3 rounded-xl text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Credit per AI Call</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">₹</span>
                            <input type="number" step="0.01" name="credit_per_ai_call" value="0.50"
                                class="input-dark w-full pl-8 pr-4 py-3 rounded-xl text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Low Credit Alert (%)</label>
                        <input type="number" name="low_credit_threshold" value="10"
                            class="input-dark w-full px-4 py-3 rounded-xl text-white">
                    </div>
                </div>
            </div>
        </div>

        <!-- API Configuration -->
        <div class="glass rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-teal-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white">Default WhatsApp API (Evolution)</h3>
                        <p class="text-gray-400 text-sm">Shared Evolution API for tenants without custom setup</p>
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">API URL</label>
                    <input type="url" name="evolution_api_url" placeholder="https://your-evolution-api.com"
                        class="input-dark w-full px-4 py-3 rounded-xl text-white placeholder-gray-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">API Key</label>
                    <input type="password" name="evolution_api_key" placeholder="Enter API key"
                        class="input-dark w-full px-4 py-3 rounded-xl text-white placeholder-gray-500">
                </div>
            </div>
        </div>

        <!-- AI Configuration Link -->
        <div class="glass rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white">AI Configuration</h3>
                        <p class="text-gray-400 text-sm">Configure Vertex AI with Google Cloud Service Account</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <a href="{{ route('superadmin.ai-config.index') }}"
                    class="btn-primary px-6 py-3 rounded-xl text-white font-medium inline-flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Open AI Configuration</span>
                </a>
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end">
            <button type="submit"
                class="btn-primary px-8 py-3 rounded-xl text-white font-medium flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Save All Settings</span>
            </button>
        </div>
    </div>
@endsection