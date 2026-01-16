@extends('superadmin.layouts.app')

@section('title', 'Add Admin')
@section('page-title', 'Add New Admin')

@section('content')
    <div class="max-w-3xl mx-auto">
        <form action="{{ route('superadmin.admins.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Client Information -->
            <div class="glass rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-white/10">
                    <h3 class="text-lg font-semibold text-white flex items-center space-x-2">
                        <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1" />
                        </svg>
                        <span>Admin Information</span>
                    </h3>
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Admin Name *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                class="input-dark w-full px-4 py-3 rounded-xl text-white placeholder-gray-500"
                                placeholder="John Doe">
                            @error('name')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Company Name</label>
                            <input type="text" name="company_name" value="{{ old('company_name') }}"
                                class="input-dark w-full px-4 py-3 rounded-xl text-white placeholder-gray-500"
                                placeholder="Company Pvt Ltd">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Email Address *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="input-dark w-full px-4 py-3 rounded-xl text-white placeholder-gray-500"
                                placeholder="client@company.com">
                            @error('email')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Phone Number</label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                class="input-dark w-full px-4 py-3 rounded-xl text-white placeholder-gray-500"
                                placeholder="+91 98765 43210">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscription Plan -->
            <div class="glass rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-white/10">
                    <h3 class="text-lg font-semibold text-white flex items-center space-x-2">
                        <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                        <span>Subscription Plan</span>
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <label class="cursor-pointer">
                            <input type="radio" name="subscription_plan" value="free" class="sr-only peer" {{ old('subscription_plan', 'free') === 'free' ? 'checked' : '' }}>
                            <div
                                class="glass-light rounded-xl p-4 border-2 border-transparent peer-checked:border-gray-400 transition-all hover:bg-white/5">
                                <h4 class="text-white font-semibold mb-2">Free</h4>
                                <p class="text-2xl font-bold text-white">₹0<span class="text-sm text-gray-400">/mo</span>
                                </p>
                                <p class="text-gray-400 text-xs mt-2">14 day trial</p>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="subscription_plan" value="basic" class="sr-only peer" {{ old('subscription_plan') === 'basic' ? 'checked' : '' }}>
                            <div
                                class="glass-light rounded-xl p-4 border-2 border-transparent peer-checked:border-blue-500 transition-all hover:bg-white/5">
                                <h4 class="text-white font-semibold mb-2">Basic</h4>
                                <p class="text-2xl font-bold text-white">₹999<span class="text-sm text-gray-400">/mo</span>
                                </p>
                                <p class="text-blue-400 text-xs mt-2">1K messages</p>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="subscription_plan" value="pro" class="sr-only peer" {{ old('subscription_plan') === 'pro' ? 'checked' : '' }}>
                            <div
                                class="glass-light rounded-xl p-4 border-2 border-transparent peer-checked:border-purple-500 transition-all hover:bg-white/5">
                                <h4 class="text-white font-semibold mb-2">Pro</h4>
                                <p class="text-2xl font-bold text-white">₹2,999<span
                                        class="text-sm text-gray-400">/mo</span></p>
                                <p class="text-purple-400 text-xs mt-2">10K messages</p>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="subscription_plan" value="enterprise" class="sr-only peer" {{ old('subscription_plan') === 'enterprise' ? 'checked' : '' }}>
                            <div
                                class="glass-light rounded-xl p-4 border-2 border-transparent peer-checked:border-yellow-500 transition-all hover:bg-white/5">
                                <h4 class="text-white font-semibold mb-2">Enterprise</h4>
                                <p class="text-2xl font-bold text-white">Custom</p>
                                <p class="text-yellow-400 text-xs mt-2">Unlimited</p>
                            </div>
                        </label>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Initial Credits</label>
                        <input type="number" name="initial_credits" value="{{ old('initial_credits', 100) }}" min="0"
                            class="input-dark w-full px-4 py-3 rounded-xl text-white placeholder-gray-500"
                            placeholder="100">
                        <p class="text-gray-500 text-xs mt-1">Number of credits to assign initially</p>
                    </div>
                </div>
            </div>

            <!-- Admin Account -->
            <div class="glass rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-white/10">
                    <h3 class="text-lg font-semibold text-white flex items-center space-x-2">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>Admin Account</span>
                    </h3>
                    <p class="text-gray-400 text-sm mt-1">This account will be used by the admin to login</p>
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Admin Email *</label>
                            <input type="email" name="admin_email" value="{{ old('admin_email') }}" required
                                class="input-dark w-full px-4 py-3 rounded-xl text-white placeholder-gray-500"
                                placeholder="admin@company.com">
                            @error('admin_email')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Admin Password *</label>
                            <input type="password" name="admin_password" required
                                class="input-dark w-full px-4 py-3 rounded-xl text-white placeholder-gray-500"
                                placeholder="••••••••">
                            @error('admin_password')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-between">
                <a href="{{ route('superadmin.admins.index') }}" class="text-gray-400 hover:text-white transition-colors">
                    ← Back to Admins
                </a>
                <button type="submit"
                    class="btn-primary px-8 py-3 rounded-xl text-white font-semibold flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span>Create Admin</span>
                </button>
            </div>
        </form>
    </div>
@endsection
