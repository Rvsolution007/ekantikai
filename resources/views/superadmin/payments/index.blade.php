@extends('superadmin.layouts.app')

@section('title', 'Payments')
@section('page-title', 'Payment History')

@section('content')
    <div class="space-y-6">
        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="glass-light rounded-xl p-4">
                <p class="text-gray-400 text-sm">Total Revenue</p>
                <p class="text-2xl font-bold text-white">₹{{ number_format($payments->sum('amount'), 0) }}</p>
            </div>
            <div class="glass-light rounded-xl p-4">
                <p class="text-gray-400 text-sm">This Month</p>
                <p class="text-2xl font-bold text-green-400">
                    ₹{{ number_format($payments->where('created_at', '>=', now()->startOfMonth())->sum('amount'), 0) }}</p>
            </div>
            <div class="glass-light rounded-xl p-4">
                <p class="text-gray-400 text-sm">Successful</p>
                <p class="text-2xl font-bold text-blue-400">{{ $payments->where('status', 'success')->count() }}</p>
            </div>
            <div class="glass-light rounded-xl p-4">
                <p class="text-gray-400 text-sm">Pending</p>
                <p class="text-2xl font-bold text-yellow-400">{{ $payments->where('status', 'pending')->count() }}</p>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="glass rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <h3 class="text-lg font-semibold text-white">All Payments</h3>
            </div>
            <table class="w-full">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Admin</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Amount</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Credits</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Method</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($payments as $payment)
                        <tr class="table-row">
                            <td class="px-6 py-4 text-white">{{ $payment->created_at->format('d M Y, h:i A') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-primary-500/20 flex items-center justify-center">
                                        <span
                                            class="text-primary-400 text-sm font-medium">{{ substr($payment->tenant->name ?? 'U', 0, 2) }}</span>
                                    </div>
                                    <span class="text-white">{{ $payment->tenant->name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-white font-semibold">{{ $payment->formatted_amount }}</td>
                            <td class="px-6 py-4 text-green-400">+{{ number_format($payment->credits_added) }}</td>
                            <td class="px-6 py-4 text-gray-400">{{ ucfirst($payment->payment_method) }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs font-medium rounded-lg {{ $payment->status_badge }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                </svg>
                                <p>No payments yet</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
@endsection