@extends('superadmin.layouts.app')

@section('title', 'Edit Admin')

@section('content')
    <style>
        /* Dark theme form styles */
        .dark-input {
            background: rgba(30, 41, 59, 0.8) !important;
            border: 1px solid rgba(148, 163, 184, 0.2) !important;
            color: #fff !important;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            width: 100%;
            transition: all 0.3s ease;
        }

        .dark-input:focus {
            outline: none;
            border-color: rgba(139, 92, 246, 0.5) !important;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        }

        .dark-input::placeholder {
            color: rgba(148, 163, 184, 0.5);
        }

        .dark-select {
            background: rgba(30, 41, 59, 0.8) !important;
            border: 1px solid rgba(148, 163, 184, 0.2) !important;
            color: #fff !important;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            width: 100%;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }

        .dark-select:focus {
            outline: none;
            border-color: rgba(139, 92, 246, 0.5) !important;
        }

        .dark-select option {
            background: #1e293b;
            color: #fff;
        }

        .dark-textarea {
            background: rgba(30, 41, 59, 0.8) !important;
            border: 1px solid rgba(148, 163, 184, 0.2) !important;
            color: #fff !important;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            width: 100%;
            resize: vertical;
            min-height: 80px;
        }

        .dark-textarea:focus {
            outline: none;
            border-color: rgba(139, 92, 246, 0.5) !important;
        }

        .dark-textarea::placeholder {
            color: rgba(148, 163, 184, 0.5);
        }

        .dark-date {
            background: rgba(30, 41, 59, 0.8) !important;
            border: 1px solid rgba(148, 163, 184, 0.2) !important;
            color: #fff !important;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            width: 100%;
            color-scheme: dark;
        }

        .dark-date:focus {
            outline: none;
            border-color: rgba(139, 92, 246, 0.5) !important;
        }
    </style>

    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white mb-2">Edit Admin</h1>
                <p class="text-gray-400">Update {{ $tenant->name }}'s information</p>
            </div>
            <a href="{{ route('superadmin.tenants.show', $tenant) }}"
                class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg inline-flex items-center transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Admin
            </a>
        </div>
    </div>

    <form action="{{ route('superadmin.tenants.update', $tenant) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Company Information -->
            <div class="glass-card p-6 rounded-xl">
                <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Company Information
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-300 text-sm font-medium mb-2">Admin Name *</label>
                        <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required
                            class="dark-input @error('name') border-red-500 @enderror" placeholder="Enter client name">
                        @error('name')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-300 text-sm font-medium mb-2">Email Address *</label>
                        <input type="email" name="email" value="{{ old('email', $tenant->email) }}" required
                            class="dark-input @error('email') border-red-500 @enderror" placeholder="client@company.com">
                        @error('email')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-300 text-sm font-medium mb-2">Phone Number</label>
                        <input type="tel" name="phone" value="{{ old('phone', $tenant->phone) }}" class="dark-input"
                            placeholder="+91 98765 43210">
                    </div>

                    <div>
                        <label class="block text-gray-300 text-sm font-medium mb-2">Domain</label>
                        <input type="text" name="domain" value="{{ old('domain', $tenant->domain) }}" class="dark-input"
                            placeholder="company.example.com">
                    </div>

                    <div>
                        <label class="block text-gray-300 text-sm font-medium mb-2">Address</label>
                        <textarea name="address" rows="3" class="dark-textarea"
                            placeholder="Enter business address">{{ old('address', $tenant->address) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Subscription & Status -->
            <div class="space-y-8">
                <div class="glass-card p-6 rounded-xl">
                    <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                        Subscription Plan
                    </h3>

                    <div class="grid grid-cols-2 gap-4">
                        <label class="cursor-pointer">
                            <input type="radio" name="subscription_plan" value="free" class="hidden peer" {{ old('subscription_plan', $tenant->subscription_plan) == 'free' ? 'checked' : '' }}>
                            <div
                                class="p-4 rounded-xl border-2 border-gray-700 peer-checked:border-purple-500 peer-checked:bg-purple-500/10 transition-all hover:border-gray-600">
                                <p class="text-white font-semibold">Free</p>
                                <p class="text-2xl font-bold text-white">₹0<span class="text-sm text-gray-400">/mo</span>
                                </p>
                                <p class="text-gray-400 text-sm mt-1">14 day trial</p>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="subscription_plan" value="basic" class="hidden peer" {{ old('subscription_plan', $tenant->subscription_plan) == 'basic' ? 'checked' : '' }}>
                            <div
                                class="p-4 rounded-xl border-2 border-gray-700 peer-checked:border-purple-500 peer-checked:bg-purple-500/10 transition-all hover:border-gray-600">
                                <p class="text-white font-semibold">Basic</p>
                                <p class="text-2xl font-bold text-white">₹999<span class="text-sm text-gray-400">/mo</span>
                                </p>
                                <p class="text-green-400 text-sm mt-1">5K messages</p>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="subscription_plan" value="pro" class="hidden peer" {{ old('subscription_plan', $tenant->subscription_plan) == 'pro' ? 'checked' : '' }}>
                            <div
                                class="p-4 rounded-xl border-2 border-gray-700 peer-checked:border-purple-500 peer-checked:bg-purple-500/10 transition-all hover:border-gray-600">
                                <p class="text-white font-semibold">Pro</p>
                                <p class="text-2xl font-bold text-white">₹2,999<span
                                        class="text-sm text-gray-400">/mo</span></p>
                                <p class="text-blue-400 text-sm mt-1">50K messages</p>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="subscription_plan" value="enterprise" class="hidden peer" {{ old('subscription_plan', $tenant->subscription_plan) == 'enterprise' ? 'checked' : '' }}>
                            <div
                                class="p-4 rounded-xl border-2 border-gray-700 peer-checked:border-purple-500 peer-checked:bg-purple-500/10 transition-all hover:border-gray-600">
                                <p class="text-white font-semibold">Enterprise</p>
                                <p class="text-2xl font-bold text-white">Custom</p>
                                <p class="text-purple-400 text-sm mt-1">Unlimited</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="glass-card p-6 rounded-xl">
                    <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Status & Settings
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Account Status</label>
                            <select name="status" class="dark-select">
                                <option value="active" {{ old('status', $tenant->status) == 'active' ? 'selected' : '' }}>
                                    Active</option>
                                <option value="trial" {{ old('status', $tenant->status) == 'trial' ? 'selected' : '' }}>Trial
                                </option>
                                <option value="suspended" {{ old('status', $tenant->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="inactive" {{ old('status', $tenant->status) == 'inactive' ? 'selected' : '' }}>
                                    Inactive</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Subscription Ends At</label>
                            <input type="date" name="subscription_ends_at"
                                value="{{ old('subscription_ends_at', $tenant->subscription_ends_at ? $tenant->subscription_ends_at->format('Y-m-d') : '') }}"
                                class="dark-date">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- WhatsApp Configuration -->
        <div class="glass-card p-6 rounded-xl mt-8">
            <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                WhatsApp Configuration (Optional)
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-300 text-sm font-medium mb-2">WhatsApp API URL</label>
                    <input type="url" name="whatsapp_api_url"
                        value="{{ old('whatsapp_api_url', $tenant->settings['whatsapp_api_url'] ?? '') }}"
                        class="dark-input" placeholder="https://api.evolution.com">
                </div>

                <div>
                    <label class="block text-gray-300 text-sm font-medium mb-2">WhatsApp API Key</label>
                    <input type="text" name="whatsapp_api_key"
                        value="{{ old('whatsapp_api_key', $tenant->settings['whatsapp_api_key'] ?? '') }}"
                        class="dark-input" placeholder="Enter API key">
                </div>

                <div>
                    <label class="block text-gray-300 text-sm font-medium mb-2">WhatsApp Instance</label>
                    <input type="text" name="whatsapp_instance"
                        value="{{ old('whatsapp_instance', $tenant->settings['whatsapp_instance'] ?? '') }}"
                        class="dark-input" placeholder="Instance name">
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="mt-8 flex justify-end space-x-4">
            <a href="{{ route('superadmin.tenants.show', $tenant) }}"
                class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition-colors">
                Cancel
            </a>
            <button type="submit" class="btn-gradient px-8 py-3 rounded-lg inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Save Changes
            </button>
        </div>
    </form>

    @if(session('success'))
        <script>
            alert('{{ session('success') }}');
        </script>
    @endif
@endsection