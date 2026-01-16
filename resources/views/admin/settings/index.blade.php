@extends('admin.layouts.app')

@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')

    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
        @csrf

        <!-- WhatsApp Settings (Evolution API) -->
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
                        <h3 class="text-lg font-semibold text-white">WhatsApp Connection (Evolution API)</h3>
                        <p class="text-gray-400 text-sm">Connect your Hostinger VPS Evolution API</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <!-- Connection Status -->
                <div class="glass-light rounded-xl p-4 mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span id="connectionStatus"
                                class="w-3 h-3 rounded-full {{ $whatsappConnected ?? false ? 'bg-green-400 animate-pulse' : 'bg-red-400' }}"></span>
                            <span id="connectionText"
                                class="text-white font-medium">{{ $whatsappConnected ?? false ? 'Connected' : 'Not Connected' }}</span>
                        </div>
                        <button type="button" id="testConnectionBtn" onclick="testConnection()"
                            class="btn-primary px-4 py-2 rounded-xl text-white text-sm flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Test Connection</span>
                        </button>
                    </div>
                </div>

                <div class="space-y-4">

                    <div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Evolution API URL <span class="text-red-400">*</span>
                            </label>
                            <input type="url" name="whatsapp_api_url" value="{{ $settings['whatsapp_api_url'] ?? '' }}"
                                class="input-dark w-full px-4 py-3 rounded-xl text-white placeholder-gray-500"
                                placeholder="https://your-vps-ip:8080" required>
                            <p class="text-xs text-gray-500 mt-1">Your Hostinger VPS Evolution API URL (e.g.,
                                https://evolution.yourdomain.com)</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                API Key <span class="text-red-400">*</span>
                            </label>
                            <input type="password" name="whatsapp_api_key" value="{{ $settings['whatsapp_api_key'] ?? '' }}"
                                class="input-dark w-full px-4 py-3 rounded-xl text-white placeholder-gray-500"
                                placeholder="Your Evolution API Global Key" required>
                            <p class="text-xs text-gray-500 mt-1">AUTHENTICATION_API_KEY from your Evolution API .env file
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Instance Name <span class="text-red-400">*</span>
                            </label>
                            <input type="text" name="whatsapp_instance" value="{{ $settings['whatsapp_instance'] ?? '' }}"
                                class="input-dark w-full px-4 py-3 rounded-xl text-white placeholder-gray-500"
                                placeholder="datsun" required>
                            <p class="text-xs text-gray-500 mt-1">The WhatsApp instance name you created in Evolution API
                            </p>
                        </div>

                    </div>

                    <!-- QR Code Section (shown when not connected) -->
                    <div id="qrCodeSection" class="hidden glass-light rounded-xl p-6 text-center">
                        <h4 class="text-white font-medium mb-4">Scan QR Code to Connect WhatsApp</h4>
                        <div id="qrCodeContainer" class="flex justify-center mb-4">
                            <!-- QR Code will be loaded here -->
                        </div>
                        <p class="text-gray-400 text-sm">Open WhatsApp on your phone → Settings → Linked Devices → Link a
                            Device</p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="button" onclick="getQrCode()"
                            class="px-6 py-3 rounded-xl bg-green-600 text-white font-medium hover:bg-green-700 transition-colors">
                            Get QR Code
                        </button>
                        <button type="button" onclick="disconnectWhatsApp()"
                            class="px-6 py-3 rounded-xl bg-red-600 text-white font-medium hover:bg-red-700 transition-colors">
                            Disconnect & Rescan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Settings -->
        <div class="glass rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white">AI Configuration</h3>
                        <p class="text-gray-400 text-sm">Configure your AI chatbot behavior</p>
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">AI Personality / System Prompt</label>
                    <textarea name="ai_system_prompt" rows="4"
                        class="input-dark w-full px-4 py-3 rounded-xl text-white placeholder-gray-500 resize-none"
                        placeholder="You are a helpful sales assistant...">{{ $settings['ai_system_prompt'] ?? '' }}</textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Response Tone</label>
                        <select name="ai_tone" class="input-dark w-full px-4 py-3 rounded-xl text-white">
                            <option value="professional" {{ ($settings['ai_tone'] ?? 'friendly') == 'professional' ? 'selected' : '' }}>Professional</option>
                            <option value="friendly" {{ ($settings['ai_tone'] ?? 'friendly') == 'friendly' ? 'selected' : '' }}>Friendly</option>
                            <option value="casual" {{ ($settings['ai_tone'] ?? 'friendly') == 'casual' ? 'selected' : '' }}>
                                Casual</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Max Response Length</label>
                        <select name="ai_max_length" class="input-dark w-full px-4 py-3 rounded-xl text-white">
                            <option value="short" {{ ($settings['ai_max_length'] ?? 'medium') == 'short' ? 'selected' : '' }}>Short (50 words)</option>
                            <option value="medium" {{ ($settings['ai_max_length'] ?? 'medium') == 'medium' ? 'selected' : '' }}>Medium (100 words)</option>
                            <option value="long" {{ ($settings['ai_max_length'] ?? 'medium') == 'long' ? 'selected' : '' }}>Long (200 words)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Business Settings -->
        <div class="glass rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white">Business Information</h3>
                        <p class="text-gray-400 text-sm">Your business details for the chatbot</p>
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Business Name</label>
                        <input type="text" name="business_name" value="{{ $settings['business_name'] ?? '' }}"
                            class="input-dark w-full px-4 py-3 rounded-xl text-white placeholder-gray-500"
                            placeholder="Your Company Name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Contact Email</label>
                        <input type="email" name="business_email" value="{{ $settings['business_email'] ?? '' }}"
                            class="input-dark w-full px-4 py-3 rounded-xl text-white placeholder-gray-500"
                            placeholder="contact@company.com">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Business Hours</label>
                    <input type="text" name="business_hours" value="{{ $settings['business_hours'] ?? '' }}"
                        class="input-dark w-full px-4 py-3 rounded-xl text-white placeholder-gray-500"
                        placeholder="Mon-Sat: 9AM - 6PM">
                </div>
            </div>
        </div>

        <!-- Bot Control Settings -->
        <div class="glass rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-pink-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white">Bot Control</h3>
                        <p class="text-gray-400 text-sm">Control bot via WhatsApp commands</p>
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Bot Control WhatsApp Number</label>
                    <input type="text" name="bot_control_number" value="{{ $settings['bot_control_number'] ?? '' }}"
                        class="input-dark w-full px-4 py-3 rounded-xl text-white placeholder-gray-500"
                        placeholder="919876543210">
                    <p class="text-gray-500 text-xs mt-2">
                        <strong>How it works:</strong> Send a message from this number to your bot's WhatsApp with format:
                        <br><code class="bg-gray-800 px-2 py-1 rounded">919876543210 stop</code> - Stop bot for that
                        customer
                        <br><code class="bg-gray-800 px-2 py-1 rounded">919876543210 start</code> - Resume bot for that
                        customer
                    </p>
                </div>
            </div>
        </div>

        <!-- Lead Settings -->
        <div class="glass rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white">Lead Settings</h3>
                        <p class="text-gray-400 text-sm">Configure how leads are created from bot conversations</p>
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Lead Timeout (Hours)</label>
                    <input type="number" name="lead_timeout_hours" value="{{ $settings['lead_timeout_hours'] ?? 24 }}"
                        min="1" max="720" class="input-dark w-full px-4 py-3 rounded-xl text-white placeholder-gray-500"
                        placeholder="24">
                    <p class="text-gray-500 text-xs mt-2">
                        If a user responds after this many hours, a <strong>new lead</strong> will be created.
                        If they respond within this time, the existing lead will be updated.
                    </p>
                </div>
            </div>
        </div>

        <!-- Followup Settings -->
        <div class="glass rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-yellow-500 to-orange-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white">Followup Settings</h3>
                        <p class="text-gray-400 text-sm">Automatic followup message timing</p>
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Followup Delay (Minutes)</label>
                    <div class="flex items-center gap-4">
                        <input type="number" name="followup_delay_minutes"
                            value="{{ $settings['followup_delay_minutes'] ?? 60 }}" min="5" max="10080"
                            class="w-32 input-dark px-4 py-3 rounded-xl text-white placeholder-gray-500">
                        <div class="flex gap-2">
                            <button type="button"
                                onclick="document.querySelector('input[name=followup_delay_minutes]').value = 30"
                                class="px-3 py-2 bg-gray-600/20 text-gray-400 rounded-lg hover:bg-gray-600/40 text-sm">30m</button>
                            <button type="button"
                                onclick="document.querySelector('input[name=followup_delay_minutes]').value = 60"
                                class="px-3 py-2 bg-gray-600/20 text-gray-400 rounded-lg hover:bg-gray-600/40 text-sm">1h</button>
                            <button type="button"
                                onclick="document.querySelector('input[name=followup_delay_minutes]').value = 1440"
                                class="px-3 py-2 bg-gray-600/20 text-gray-400 rounded-lg hover:bg-gray-600/40 text-sm">24h</button>
                        </div>
                    </div>
                    <p class="text-gray-500 text-xs mt-2">
                        If customer doesn't respond within this time, automatic followup message will be sent from your
                        templates.
                        <a href="{{ route('admin.followup-templates.index') }}"
                            class="text-primary-400 hover:underline">Manage Templates →</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Save Button at Bottom -->
        <div class="flex justify-end">
            <button type="submit" class="btn-primary px-8 py-4 rounded-xl text-white font-medium text-lg">
                💾 Save All Settings
            </button>
        </div>

    </form>
    </div>

    @push('scripts')
        <script>
            async function testConnection() {
                const btn = document.getElementById('testConnectionBtn');
                const status = document.getElementById('connectionStatus');
                const text = document.getElementById('connectionText');

                btn.disabled = true;
                btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg><span>Testing...</span>';

                try {
                    const response = await fetch('{{ route("admin.settings.test-connection") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await response.json();

                    if (data.connected) {
                        status.className = 'w-3 h-3 rounded-full bg-green-400 animate-pulse';
                        text.textContent = 'Connected';
                        alert('✅ Connection successful! WhatsApp is connected.');
                    } else {
                        status.className = 'w-3 h-3 rounded-full bg-red-400';
                        text.textContent = 'Not Connected';
                        alert('❌ ' + (data.message || 'Connection failed. Please check your settings.'));
                    }
                } catch (error) {
                    status.className = 'w-3 h-3 rounded-full bg-red-400';
                    text.textContent = 'Error';
                    alert('❌ Error: ' + error.message);
                }

                btn.disabled = false;
                btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg><span>Test Connection</span>';
            }

            async function getQrCode() {
                const qrSection = document.getElementById('qrCodeSection');
                const qrContainer = document.getElementById('qrCodeContainer');

                qrContainer.innerHTML = '<div class="text-gray-400">Loading QR Code...</div>';
                qrSection.classList.remove('hidden');

                try {
                    const response = await fetch('{{ route("admin.settings.get-qr") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await response.json();

                    if (data.qrcode) {
                        qrContainer.innerHTML = '<img src="' + data.qrcode + '" alt="QR Code" class="max-w-xs rounded-xl">';
                    } else if (data.pairingCode) {
                        qrContainer.innerHTML = '<div class="text-white text-2xl font-mono bg-gray-800 px-6 py-4 rounded-xl">' + data.pairingCode + '</div><p class="text-gray-400 mt-2">Enter this code in WhatsApp</p>';
                    } else if (data.connected) {
                        qrContainer.innerHTML = '<div class="text-green-400 text-lg">✅ Already Connected! Click "Disconnect & Rescan" to get a new QR code.</div>';
                    } else {
                        qrContainer.innerHTML = '<div class="text-red-400">' + (data.message || 'Failed to get QR code') + '</div>';
                    }
                } catch (error) {
                    qrContainer.innerHTML = '<div class="text-red-400">Error: ' + error.message + '</div>';
                }
            }

            async function disconnectWhatsApp() {
                if (!confirm('Are you sure you want to disconnect WhatsApp? You will need to scan QR code again.')) {
                    return;
                }

                const status = document.getElementById('connectionStatus');
                const text = document.getElementById('connectionText');

                try {
                    const response = await fetch('{{ route("admin.settings.disconnect") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        status.className = 'w-3 h-3 rounded-full bg-red-400';
                        text.textContent = 'Disconnected';
                        alert('✅ ' + data.message);
                        // Auto-get QR code after disconnect
                        getQrCode();
                    } else {
                        alert('❌ ' + (data.message || 'Failed to disconnect'));
                    }
                } catch (error) {
                    alert('❌ Error: ' + error.message);
                }
            }
        </script>
    @endpush
@endsection