@extends('superadmin.layouts.app')

@section('title', 'Credits')
@section('page-title', 'Credit Management')

@section('content')
    <div class="space-y-6">
        <!-- Credits Table -->
        <div class="glass rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <h3 class="text-lg font-semibold text-white">All Admin Credits</h3>
            </div>
            <table class="w-full">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Admin</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Total Credits</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Used</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Available</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Usage</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($credits as $credit)
                        <tr class="table-row">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center">
                                        <span
                                            class="text-white font-medium">{{ substr($credit->tenant->name ?? 'U', 0, 2) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-white font-medium">{{ $credit->tenant->name ?? 'Unknown' }}</p>
                                        <p class="text-gray-400 text-sm">{{ $credit->tenant->email ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-white font-semibold">{{ number_format($credit->total_credits) }}</td>
                            <td class="px-6 py-4 text-red-400">{{ number_format($credit->used_credits) }}</td>
                            <td class="px-6 py-4 text-green-400 font-semibold">{{ number_format($credit->available_credits) }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <div class="w-24 h-2 bg-white/10 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-primary-500 to-purple-500"
                                            style="width: {{ min(100, $credit->usage_percentage) }}%"></div>
                                    </div>
                                    <span class="text-gray-400 text-sm">{{ $credit->usage_percentage }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    onclick="openAddCredits({{ $credit->tenant_id }}, '{{ $credit->tenant->name ?? 'Unknown' }}')"
                                    class="px-4 py-2 text-sm bg-green-500/20 text-green-400 rounded-lg hover:bg-green-500/30 transition-colors">
                                    + Add Credits
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1" />
                                </svg>
                                <p>No admins with credits yet</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">
                {{ $credits->links() }}
            </div>
        </div>
    </div>

    <!-- Add Credits Modal -->
    <div id="addCreditsModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        x-data="{ open: false }">
        <div class="glass rounded-2xl p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold text-white mb-4">Add Credits</h3>
            <p class="text-gray-400 mb-4">Adding credits to: <span id="modalAdminName"
                    class="text-white font-medium"></span></p>

            <form id="addCreditsForm" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Credit Amount</label>
                        <input type="number" name="amount" min="1" required
                            class="input-dark w-full px-4 py-3 rounded-xl text-white" placeholder="Enter credit amount">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Notes (Optional)</label>
                        <textarea name="notes" rows="2"
                            class="input-dark w-full px-4 py-3 rounded-xl text-white resize-none"
                            placeholder="Reason for adding credits"></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeAddCredits()"
                        class="px-6 py-2 text-gray-400 hover:text-white transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary px-6 py-2 rounded-xl text-white font-medium">
                        Add Credits
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function openAddCredits(tenantId, adminName) {
                document.getElementById('modalAdminName').textContent = adminName;
                document.getElementById('addCreditsForm').action = `/superadmin/tenants/${tenantId}/add-credits`;
                document.getElementById('addCreditsModal').classList.remove('hidden');
            }

            function closeAddCredits() {
                document.getElementById('addCreditsModal').classList.add('hidden');
            }
        </script>
    @endpush
@endsection