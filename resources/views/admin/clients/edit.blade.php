@extends('admin.layouts.app')

@section('title', 'Edit Client - ' . $client->display_name)
@section('page-title', 'Edit Client')

@section('content')
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('admin.clients.show', $client) }}"
                class="p-2 rounded-lg bg-white/10 text-gray-400 hover:text-white hover:bg-white/20 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-white">Edit Client</h1>
        </div>

        <!-- Form -->
        <form action="{{ route('admin.clients.update', $client) }}" method="POST" class="glass rounded-2xl p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label class="block text-gray-300 text-sm mb-2">Name</label>
                    <input type="text" name="name" value="{{ old('name', $client->name) }}"
                        class="w-full bg-dark-300 border border-white/10 rounded-lg px-4 py-2 text-white"
                        placeholder="Client name">
                    @error('name') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Business Name -->
                <div>
                    <label class="block text-gray-300 text-sm mb-2">Business Name</label>
                    <input type="text" name="business_name" value="{{ old('business_name', $client->business_name) }}"
                        class="w-full bg-dark-300 border border-white/10 rounded-lg px-4 py-2 text-white"
                        placeholder="Company/Business name">
                    @error('business_name') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- GST Number -->
                <div>
                    <label class="block text-gray-300 text-sm mb-2">GST Number</label>
                    <input type="text" name="gst_number" value="{{ old('gst_number', $client->gst_number) }}"
                        class="w-full bg-dark-300 border border-white/10 rounded-lg px-4 py-2 text-white"
                        placeholder="GST Number">
                    @error('gst_number') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-gray-300 text-sm mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email', $client->email) }}"
                        class="w-full bg-dark-300 border border-white/10 rounded-lg px-4 py-2 text-white"
                        placeholder="Email address">
                    @error('email') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- City -->
                <div>
                    <label class="block text-gray-300 text-sm mb-2">City</label>
                    <input type="text" name="city" value="{{ old('city', $client->city) }}"
                        class="w-full bg-dark-300 border border-white/10 rounded-lg px-4 py-2 text-white"
                        placeholder="City">
                    @error('city') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- State -->
                <div>
                    <label class="block text-gray-300 text-sm mb-2">State</label>
                    <input type="text" name="state" value="{{ old('state', $client->state) }}"
                        class="w-full bg-dark-300 border border-white/10 rounded-lg px-4 py-2 text-white"
                        placeholder="State">
                    @error('state') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Address -->
            <div>
                <label class="block text-gray-300 text-sm mb-2">Address</label>
                <textarea name="address" rows="2"
                    class="w-full bg-dark-300 border border-white/10 rounded-lg px-4 py-2 text-white"
                    placeholder="Full address">{{ old('address', $client->address) }}</textarea>
                @error('address') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-gray-300 text-sm mb-2">Notes</label>
                <textarea name="notes" rows="3"
                    class="w-full bg-dark-300 border border-white/10 rounded-lg px-4 py-2 text-white"
                    placeholder="Internal notes about client">{{ old('notes', $client->notes) }}</textarea>
                @error('notes') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Actions -->
            <div class="flex gap-4 pt-4">
                <button type="submit" class="btn-gradient px-6 py-2 rounded-lg flex-1">
                    Save Changes
                </button>
                <a href="{{ route('admin.clients.show', $client) }}"
                    class="px-6 py-2 rounded-lg border border-white/10 text-gray-300 hover:text-white hover:border-white/20 transition-colors text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection