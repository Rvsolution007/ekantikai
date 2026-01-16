@extends('superadmin.layouts.app')

@section('title', 'Admins')
@section('page-title', 'Admin Management')

@section('content')
    <div class="space-y-6">
        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="glass-light rounded-xl p-4">
                <p class="text-gray-400 text-sm">Total Admins</p>
                <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
            </div>
            <div class="glass-light rounded-xl p-4">
                <p class="text-gray-400 text-sm">Active</p>
                <p class="text-2xl font-bold text-green-400">{{ $stats['active'] }}</p>
            </div>
            <div class="glass-light rounded-xl p-4">
                <p class="text-gray-400 text-sm">Trial</p>
                <p class="text-2xl font-bold text-yellow-400">{{ $stats['trial'] }}</p>
            </div>
            <div class="glass-light rounded-xl p-4">
                <p class="text-gray-400 text-sm">Paid</p>
                <p class="text-2xl font-bold text-purple-400">{{ $stats['paid'] }}</p>
            </div>
        </div>

        <!-- Filters & Actions -->
        <div class="glass rounded-xl p-4">
            <form action="{{ route('superadmin.tenants.index') }}" method="GET" class="flex flex-wrap gap-4 items-center">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search admins..."
                        class="input-dark w-full px-4 py-2 rounded-xl text-white placeholder-gray-500">
                </div>
                <select name="plan" class="input-dark px-4 py-2 rounded-xl text-white">
                    <option value="">All Plans</option>
                    <option value="free" {{ request('plan') === 'free' ? 'selected' : '' }}>Free</option>
                    <option value="basic" {{ request('plan') === 'basic' ? 'selected' : '' }}>Basic</option>
                    <option value="pro" {{ request('plan') === 'pro' ? 'selected' : '' }}>Pro</option>
                    <option value="enterprise" {{ request('plan') === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                </select>
                <select name="status" class="input-dark px-4 py-2 rounded-xl text-white">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" class="btn-primary px-6 py-2 rounded-xl text-white font-medium">
                    Filter
                </button>
                <a href="{{ route('superadmin.tenants.create') }}"
                    class="btn-primary px-6 py-2 rounded-xl text-white font-medium flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span>Add Admin</span>
                </a>
            </form>
        </div>

        <!-- Clients Table -->
        <div class="glass rounded-2xl overflow-hidden">
            <table class="w-full">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Admin
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Credits
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">WhatsApp
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Product
                            Images
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($tenants as $tenant)
                        <tr class="table-row">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center flex-shrink-0">
                                        <span class="text-white font-medium">{{ substr($tenant->name, 0, 2) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-white font-medium">{{ $tenant->name }}</p>
                                        <p class="text-gray-400 text-sm">{{ $tenant->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-3 py-1 text-xs font-medium rounded-lg
                                                            {{ $tenant->subscription_plan === 'enterprise' ? 'bg-yellow-500/20 text-yellow-400' : '' }}
                                                            {{ $tenant->subscription_plan === 'pro' ? 'bg-purple-500/20 text-purple-400' : '' }}
                                                            {{ $tenant->subscription_plan === 'basic' ? 'bg-blue-500/20 text-blue-400' : '' }}
                                                            {{ $tenant->subscription_plan === 'free' ? 'bg-gray-500/20 text-gray-400' : '' }}">
                                    {{ ucfirst($tenant->subscription_plan) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($tenant->credits)
                                    <div class="flex items-center space-x-2">
                                        <div class="w-20 h-2 bg-white/10 rounded-full overflow-hidden">
                                            <div class="h-full bg-gradient-to-r from-primary-500 to-purple-500"
                                                style="width: {{ min(100, $tenant->credits->usage_percentage) }}%"></div>
                                        </div>
                                        <span
                                            class="text-gray-400 text-sm">{{ number_format($tenant->credits->available_credits) }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($tenant->whatsapp_connected)
                                    <span class="flex items-center text-green-400 text-sm">
                                        <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                                        Connected
                                    </span>
                                @else
                                    <span class="text-gray-500 text-sm">Not connected</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-3 py-1 text-xs font-medium rounded-lg {{ $tenant->is_active ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                                    {{ $tenant->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <form action="{{ route('superadmin.tenants.toggle-product-images', $tenant) }}" method="POST"
                                    class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $tenant->send_product_images ? 'bg-primary-500' : 'bg-gray-600' }}"
                                        title="{{ $tenant->send_product_images ? 'Disable' : 'Enable' }} product images">
                                        <span
                                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $tenant->send_product_images ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('superadmin.tenants.show', $tenant) }}"
                                        class="p-2 text-gray-400 hover:text-white transition-colors" title="View">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('superadmin.tenants.edit', $tenant) }}"
                                        class="p-2 text-gray-400 hover:text-primary-400 transition-colors" title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('superadmin.tenants.toggle-status', $tenant) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-yellow-400 transition-colors"
                                            title="{{ $tenant->is_active ? 'Deactivate' : 'Activate' }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                        </button>
                                    </form>
                                    <form action="{{ route('superadmin.tenants.destroy', $tenant) }}" method="POST"
                                        class="inline"
                                        onsubmit="return confirm('Are you sure you want to delete this admin? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-400 transition-colors"
                                            title="Delete">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 mb-4 text-gray-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <p class="text-lg font-medium">No admins found</p>
                                    <p class="text-sm">Get started by adding your first admin</p>
                                    <a href="{{ route('superadmin.tenants.create') }}"
                                        class="btn-primary px-6 py-2 rounded-xl text-white font-medium mt-4">
                                        Add Admin
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $tenants->withQueryString()->links() }}
        </div>
    </div>
@endsection