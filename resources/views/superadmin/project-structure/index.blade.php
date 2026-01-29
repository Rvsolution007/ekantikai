@extends('superadmin.layouts.app')

@section('title', 'Project Structure')
@section('page-title', 'Project Structure - System Samajhein')

@section('content')
    <div class="space-y-8">
        <!-- Header -->
        <div class="glass-card p-6 rounded-2xl">
            <h2 class="text-2xl font-bold gradient-text mb-2">Project Structure Documentation</h2>
            <p class="text-gray-400">Is page me aap dekhenge ki humara chatbot system kaise kaam karta hai - simple Hindi
                me, bina coding ke!</p>
        </div>

        <!-- Section 1: Overall System Flow -->
        <div class="glass-card p-6 rounded-2xl" id="overall-flow">
            <div class="flex items-center space-x-3 mb-6">
                <div
                    class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white">1. Poora System Kaise Kaam Karta Hai</h3>
            </div>

            <p class="text-gray-400 mb-6">Jab koi customer WhatsApp pe message bhejta hai, toh ye process hota hai:</p>

            <!-- Flow Diagram -->
            <div class="bg-dark-200 rounded-xl p-6 overflow-x-auto">
                <div class="flex flex-wrap items-center justify-center gap-4 min-w-max">
                    <!-- Customer -->
                    <div
                        class="flow-box bg-gradient-to-br from-green-600 to-green-700 p-4 rounded-xl text-center min-w-[120px]">
                        <div class="text-2xl mb-2">👤</div>
                        <div class="text-white font-semibold">Customer</div>
                        <div class="text-green-200 text-xs mt-1">Message bhejta hai</div>
                    </div>

                    <div class="text-2xl text-gray-500">→</div>

                    <!-- WhatsApp -->
                    <div
                        class="flow-box bg-gradient-to-br from-green-500 to-emerald-600 p-4 rounded-xl text-center min-w-[120px]">
                        <div class="text-2xl mb-2">📱</div>
                        <div class="text-white font-semibold">WhatsApp</div>
                        <div class="text-green-200 text-xs mt-1">Message receive</div>
                    </div>

                    <div class="text-2xl text-gray-500">→</div>

                    <!-- n8n Webhook -->
                    <div
                        class="flow-box bg-gradient-to-br from-orange-500 to-red-500 p-4 rounded-xl text-center min-w-[120px]">
                        <div class="text-2xl mb-2">🔗</div>
                        <div class="text-white font-semibold">n8n Webhook</div>
                        <div class="text-orange-200 text-xs mt-1">Message forward</div>
                    </div>

                    <div class="text-2xl text-gray-500">→</div>

                    <!-- Our System -->
                    <div
                        class="flow-box bg-gradient-to-br from-primary-600 to-purple-600 p-4 rounded-xl text-center min-w-[120px]">
                        <div class="text-2xl mb-2">🤖</div>
                        <div class="text-white font-semibold">Humara Bot</div>
                        <div class="text-purple-200 text-xs mt-1">Message process</div>
                    </div>

                    <div class="text-2xl text-gray-500">→</div>

                    <!-- AI -->
                    <div
                        class="flow-box bg-gradient-to-br from-blue-500 to-indigo-600 p-4 rounded-xl text-center min-w-[120px]">
                        <div class="text-2xl mb-2">🧠</div>
                        <div class="text-white font-semibold">AI (Gemini)</div>
                        <div class="text-blue-200 text-xs mt-1">Reply generate</div>
                    </div>

                    <div class="text-2xl text-gray-500">→</div>

                    <!-- Response -->
                    <div
                        class="flow-box bg-gradient-to-br from-green-600 to-green-700 p-4 rounded-xl text-center min-w-[120px]">
                        <div class="text-2xl mb-2">✅</div>
                        <div class="text-white font-semibold">Customer Ko Reply</div>
                        <div class="text-green-200 text-xs mt-1">Message milta hai</div>
                    </div>
                </div>
            </div>

            <div class="mt-4 p-4 bg-blue-900/20 border border-blue-500/30 rounded-xl">
                <p class="text-blue-300 text-sm">
                    <strong>Samjhein:</strong> Customer ka message pehle WhatsApp se n8n me jaata hai, phir humare system
                    me.
                    Humara bot AI ki madad se samajhta hai ki customer kya chahta hai, aur suitable reply bhejta hai.
                </p>
            </div>
        </div>

        <!-- Section 2: Admin Panel Sections -->
        <div class="glass-card p-6 rounded-2xl" id="admin-sections">
            <div class="flex items-center space-x-3 mb-6">
                <div
                    class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white">2. Admin Panel Ke Sections</h3>
            </div>

            <p class="text-gray-400 mb-6">Admin panel me ye sabhi sections hain aur unka kaam:</p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Dashboard -->
                <div
                    class="section-card bg-dark-200 p-5 rounded-xl hover:bg-dark-100 transition-all cursor-pointer border border-transparent hover:border-primary-500/50">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center">
                            <span class="text-xl">📊</span>
                        </div>
                        <h4 class="text-white font-semibold">Dashboard</h4>
                    </div>
                    <p class="text-gray-400 text-sm">Overview dikhata hai - kitne leads, kitni chats, aaj ki activity etc.
                    </p>
                </div>

                <!-- Leads -->
                <div
                    class="section-card bg-dark-200 p-5 rounded-xl hover:bg-dark-100 transition-all cursor-pointer border border-transparent hover:border-primary-500/50">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-green-500/20 flex items-center justify-center">
                            <span class="text-xl">👥</span>
                        </div>
                        <h4 class="text-white font-semibold">Leads</h4>
                    </div>
                    <p class="text-gray-400 text-sm">Customers ki list jinke product inquiries hain - naam, phone, products,
                        status sab yahan.</p>
                </div>

                <!-- Catalogue -->
                <div
                    class="section-card bg-dark-200 p-5 rounded-xl hover:bg-dark-100 transition-all cursor-pointer border border-transparent hover:border-primary-500/50">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center">
                            <span class="text-xl">📦</span>
                        </div>
                        <h4 class="text-white font-semibold">Catalogue</h4>
                    </div>
                    <p class="text-gray-400 text-sm">Products ki list - model number, category, size, finish etc. AI isme se
                        product dhundhta hai.</p>
                </div>

                <!-- Chats -->
                <div
                    class="section-card bg-dark-200 p-5 rounded-xl hover:bg-dark-100 transition-all cursor-pointer border border-transparent hover:border-primary-500/50">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-orange-500/20 flex items-center justify-center">
                            <span class="text-xl">💬</span>
                        </div>
                        <h4 class="text-white font-semibold">Chats</h4>
                    </div>
                    <p class="text-gray-400 text-sm">Customers ke saath WhatsApp conversations. Manual messages bhi bhej
                        sakte hain yahan se.</p>
                </div>

                <!-- Workflow Builder -->
                <div
                    class="section-card bg-dark-200 p-5 rounded-xl hover:bg-dark-100 transition-all cursor-pointer border border-transparent hover:border-primary-500/50">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-cyan-500/20 flex items-center justify-center">
                            <span class="text-xl">🔄</span>
                        </div>
                        <h4 class="text-white font-semibold">Workflow Builder</h4>
                    </div>
                    <p class="text-gray-400 text-sm">Bot ke questions set karo - kaun se sawal pehle, kaun se baad me.
                        Flowchart jaisa.</p>
                </div>

                <!-- Followups -->
                <div
                    class="section-card bg-dark-200 p-5 rounded-xl hover:bg-dark-100 transition-all cursor-pointer border border-transparent hover:border-primary-500/50">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-yellow-500/20 flex items-center justify-center">
                            <span class="text-xl">⏰</span>
                        </div>
                        <h4 class="text-white font-semibold">Followups</h4>
                    </div>
                    <p class="text-gray-400 text-sm">Automatic reminders set karo - 1 din baad, 3 din baad message bhejo
                        customer ko.</p>
                </div>

                <!-- Settings -->
                <div
                    class="section-card bg-dark-200 p-5 rounded-xl hover:bg-dark-100 transition-all cursor-pointer border border-transparent hover:border-primary-500/50">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-gray-500/20 flex items-center justify-center">
                            <span class="text-xl">⚙️</span>
                        </div>
                        <h4 class="text-white font-semibold">Settings</h4>
                    </div>
                    <p class="text-gray-400 text-sm">WhatsApp connection, bot settings, prompts, themes - sab control yahan
                        se.</p>
                </div>

                <!-- WhatsApp Users -->
                <div
                    class="section-card bg-dark-200 p-5 rounded-xl hover:bg-dark-100 transition-all cursor-pointer border border-transparent hover:border-primary-500/50">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-green-500/20 flex items-center justify-center">
                            <span class="text-xl">📋</span>
                        </div>
                        <h4 class="text-white font-semibold">WhatsApp Users</h4>
                    </div>
                    <p class="text-gray-400 text-sm">Jinse baat hui hai unki list. Bot on/off kar sakte ho kisi specific
                        user ke liye.</p>
                </div>

                <!-- Lead Status -->
                <div
                    class="section-card bg-dark-200 p-5 rounded-xl hover:bg-dark-100 transition-all cursor-pointer border border-transparent hover:border-primary-500/50">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-red-500/20 flex items-center justify-center">
                            <span class="text-xl">📌</span>
                        </div>
                        <h4 class="text-white font-semibold">Lead Status (Kanban)</h4>
                    </div>
                    <p class="text-gray-400 text-sm">Leads ko stages me track karo - New, In Progress, Won, Lost. Drag-drop
                        se move karo.</p>
                </div>
            </div>
        </div>

        <!-- Section 3: Chat Flow -->
        <div class="glass-card p-6 rounded-2xl" id="chat-flow">
            <div class="flex items-center space-x-3 mb-6">
                <div
                    class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white">3. Chat Flow - Message Kaise Process Hota Hai</h3>
            </div>

            <div class="bg-dark-200 rounded-xl p-6">
                <div class="space-y-4">
                    <!-- Step 1 -->
                    <div class="flex items-start space-x-4">
                        <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0">
                            <span class="text-white font-bold">1</span>
                        </div>
                        <div class="flex-1 bg-dark-100 p-4 rounded-xl">
                            <h4 class="text-white font-semibold mb-1">Customer Message Bhejta Hai</h4>
                            <p class="text-gray-400 text-sm">Customer WhatsApp pe likhta hai - "Profile handles dikhaao" ya
                                "9008 ka price kya hai"</p>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="flex items-start space-x-4">
                        <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center flex-shrink-0">
                            <span class="text-white font-bold">2</span>
                        </div>
                        <div class="flex-1 bg-dark-100 p-4 rounded-xl">
                            <h4 class="text-white font-semibold mb-1">Bot Check Karta Hai - Customer Kahan Hai</h4>
                            <p class="text-gray-400 text-sm">Customer ne pehle baat ki hai ya nahi? Kaunsa sawal puchha ja
                                raha hai? Customer ka state check hota hai.</p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="flex items-start space-x-4">
                        <div class="w-10 h-10 rounded-full bg-purple-500 flex items-center justify-center flex-shrink-0">
                            <span class="text-white font-bold">3</span>
                        </div>
                        <div class="flex-1 bg-dark-100 p-4 rounded-xl">
                            <h4 class="text-white font-semibold mb-1">AI Message Samajhta Hai</h4>
                            <p class="text-gray-400 text-sm">Gemini AI message padhta hai - customer kya chahta hai? Product
                                search? Price puchh raha? Ya general question?</p>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="flex items-start space-x-4">
                        <div class="w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center flex-shrink-0">
                            <span class="text-white font-bold">4</span>
                        </div>
                        <div class="flex-1 bg-dark-100 p-4 rounded-xl">
                            <h4 class="text-white font-semibold mb-1">Catalogue Me Products Dhundhe</h4>
                            <p class="text-gray-400 text-sm">Agar products dhundhne hain toh Catalogue database se matching
                                products mil jaate hain.</p>
                        </div>
                    </div>

                    <!-- Step 5 -->
                    <div class="flex items-start space-x-4">
                        <div class="w-10 h-10 rounded-full bg-cyan-500 flex items-center justify-center flex-shrink-0">
                            <span class="text-white font-bold">5</span>
                        </div>
                        <div class="flex-1 bg-dark-100 p-4 rounded-xl">
                            <h4 class="text-white font-semibold mb-1">AI Reply Generate Karta Hai</h4>
                            <p class="text-gray-400 text-sm">AI sabhi information lekar ek friendly, helpful reply banata
                                hai customer ke liye.</p>
                        </div>
                    </div>

                    <!-- Step 6 -->
                    <div class="flex items-start space-x-4">
                        <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0">
                            <span class="text-white font-bold">6</span>
                        </div>
                        <div class="flex-1 bg-dark-100 p-4 rounded-xl">
                            <h4 class="text-white font-semibold mb-1">Customer Ko Reply Bhejta Hai</h4>
                            <p class="text-gray-400 text-sm">n8n ke through WhatsApp pe message bhej diya jaata hai.
                                Products ke images bhi saath me bheje ja sakte hain.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4: Lead Management -->
        <div class="glass-card p-6 rounded-2xl" id="lead-management">
            <div class="flex items-center space-x-3 mb-6">
                <div
                    class="w-10 h-10 rounded-xl bg-gradient-to-br from-yellow-500 to-orange-500 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white">4. Lead Management - Customer Journey</h3>
            </div>

            <div class="bg-dark-200 rounded-xl p-6">
                <div class="flex flex-wrap items-center justify-center gap-6">
                    <!-- New Customer -->
                    <div class="text-center">
                        <div
                            class="w-20 h-20 mx-auto rounded-full bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center mb-3">
                            <span class="text-3xl">🆕</span>
                        </div>
                        <div class="text-white font-semibold">New Customer</div>
                        <div class="text-gray-400 text-xs mt-1">Pehli baar message kiya</div>
                    </div>

                    <div class="text-gray-500 text-2xl">→</div>

                    <!-- Questions -->
                    <div class="text-center">
                        <div
                            class="w-20 h-20 mx-auto rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center mb-3">
                            <span class="text-3xl">❓</span>
                        </div>
                        <div class="text-white font-semibold">Bot Questions</div>
                        <div class="text-gray-400 text-xs mt-1">Name, city, etc. puche</div>
                    </div>

                    <div class="text-gray-500 text-2xl">→</div>

                    <!-- Product Interest -->
                    <div class="text-center">
                        <div
                            class="w-20 h-20 mx-auto rounded-full bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center mb-3">
                            <span class="text-3xl">📦</span>
                        </div>
                        <div class="text-white font-semibold">Product Interest</div>
                        <div class="text-gray-400 text-xs mt-1">Kaunsa product chahiye</div>
                    </div>

                    <div class="text-gray-500 text-2xl">→</div>

                    <!-- Lead Created -->
                    <div class="text-center">
                        <div
                            class="w-20 h-20 mx-auto rounded-full bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center mb-3">
                            <span class="text-3xl">✅</span>
                        </div>
                        <div class="text-white font-semibold">Lead Created</div>
                        <div class="text-gray-400 text-xs mt-1">Sab info save</div>
                    </div>

                    <div class="text-gray-500 text-2xl">→</div>

                    <!-- Followup -->
                    <div class="text-center">
                        <div
                            class="w-20 h-20 mx-auto rounded-full bg-gradient-to-br from-yellow-500 to-orange-500 flex items-center justify-center mb-3">
                            <span class="text-3xl">⏰</span>
                        </div>
                        <div class="text-white font-semibold">Followup</div>
                        <div class="text-gray-400 text-xs mt-1">Automatic reminders</div>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-green-900/20 border border-green-500/30 rounded-xl">
                    <p class="text-green-300 text-sm">
                        <strong>Lead me ye info hoti hai:</strong> Customer Name, Phone, City, Products interested in,
                        Quantity, Status (New/Hot/Won/Lost)
                    </p>
                </div>
            </div>
        </div>

        <!-- Section 5: AI Integration -->
        <div class="glass-card p-6 rounded-2xl" id="ai-integration">
            <div class="flex items-center space-x-3 mb-6">
                <div
                    class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-500 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white">5. AI Kaise Kaam Karta Hai</h3>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- AI Input -->
                <div class="bg-dark-200 rounded-xl p-5">
                    <h4 class="text-white font-semibold mb-4 flex items-center">
                        <span class="mr-2">📥</span> AI Ko Kya Milta Hai (Input)
                    </h4>
                    <ul class="space-y-3">
                        <li class="flex items-start space-x-3">
                            <span class="text-green-400">✓</span>
                            <span class="text-gray-300">Customer ka current message</span>
                        </li>
                        <li class="flex items-start space-x-3">
                            <span class="text-green-400">✓</span>
                            <span class="text-gray-300">Pichli baat-cheet (chat history)</span>
                        </li>
                        <li class="flex items-start space-x-3">
                            <span class="text-green-400">✓</span>
                            <span class="text-gray-300">Customer ki info (naam, city)</span>
                        </li>
                        <li class="flex items-start space-x-3">
                            <span class="text-green-400">✓</span>
                            <span class="text-gray-300">Catalogue se products</span>
                        </li>
                        <li class="flex items-start space-x-3">
                            <span class="text-green-400">✓</span>
                            <span class="text-gray-300">Company ka system prompt</span>
                        </li>
                    </ul>
                </div>

                <!-- AI Output -->
                <div class="bg-dark-200 rounded-xl p-5">
                    <h4 class="text-white font-semibold mb-4 flex items-center">
                        <span class="mr-2">📤</span> AI Kya Return Karta Hai (Output)
                    </h4>
                    <ul class="space-y-3">
                        <li class="flex items-start space-x-3">
                            <span class="text-blue-400">→</span>
                            <span class="text-gray-300">Customer ke liye reply message</span>
                        </li>
                        <li class="flex items-start space-x-3">
                            <span class="text-blue-400">→</span>
                            <span class="text-gray-300">Extract kiya data (naam, city etc.)</span>
                        </li>
                        <li class="flex items-start space-x-3">
                            <span class="text-blue-400">→</span>
                            <span class="text-gray-300">Product confirmations</span>
                        </li>
                        <li class="flex items-start space-x-3">
                            <span class="text-blue-400">→</span>
                            <span class="text-gray-300">Next step kya hona chahiye</span>
                        </li>
                        <li class="flex items-start space-x-3">
                            <span class="text-blue-400">→</span>
                            <span class="text-gray-300">Kaunse products bhejne hain</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-4 p-4 bg-purple-900/20 border border-purple-500/30 rounded-xl">
                <p class="text-purple-300 text-sm">
                    <strong>Note:</strong> AI apne products invent nahi karta. Wo sirf wahi products suggest karta hai jo
                    Catalogue me hain.
                    Agar product nahi mila toh wo politely bata deta hai.
                </p>
            </div>
        </div>

        <!-- Section 6: Data Relationships -->
        <div class="glass-card p-6 rounded-2xl" id="data-relationships">
            <div class="flex items-center space-x-3 mb-6">
                <div
                    class="w-10 h-10 rounded-xl bg-gradient-to-br from-pink-500 to-rose-500 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white">6. Data Kaise Juda Hai (Relationships)</h3>
            </div>

            <div class="bg-dark-200 rounded-xl p-6">
                <!-- Main Entity: Admin -->
                <div class="flex flex-col items-center mb-8">
                    <div class="db-box bg-gradient-to-br from-primary-600 to-purple-600 px-8 py-4 rounded-xl text-center">
                        <div class="text-2xl mb-1">🏢</div>
                        <div class="text-white font-bold text-lg">Admin (Company)</div>
                        <div class="text-purple-200 text-xs mt-1">Ek business account</div>
                    </div>
                </div>

                <!-- Connected Entities -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <div class="text-center">
                        <div class="w-2 h-8 bg-primary-500 mx-auto mb-2"></div>
                        <div class="db-box bg-dark-100 border border-green-500/50 px-4 py-3 rounded-xl">
                            <div class="text-xl mb-1">👥</div>
                            <div class="text-white font-semibold text-sm">Customers</div>
                            <div class="text-gray-400 text-xs mt-1">Jo WhatsApp pe aaye</div>
                        </div>
                    </div>

                    <div class="text-center">
                        <div class="w-2 h-8 bg-primary-500 mx-auto mb-2"></div>
                        <div class="db-box bg-dark-100 border border-blue-500/50 px-4 py-3 rounded-xl">
                            <div class="text-xl mb-1">📦</div>
                            <div class="text-white font-semibold text-sm">Catalogue</div>
                            <div class="text-gray-400 text-xs mt-1">Products ki list</div>
                        </div>
                    </div>

                    <div class="text-center">
                        <div class="w-2 h-8 bg-primary-500 mx-auto mb-2"></div>
                        <div class="db-box bg-dark-100 border border-orange-500/50 px-4 py-3 rounded-xl">
                            <div class="text-xl mb-1">🔄</div>
                            <div class="text-white font-semibold text-sm">Workflow</div>
                            <div class="text-gray-400 text-xs mt-1">Bot ke questions</div>
                        </div>
                    </div>

                    <div class="text-center">
                        <div class="w-2 h-8 bg-primary-500 mx-auto mb-2"></div>
                        <div class="db-box bg-dark-100 border border-yellow-500/50 px-4 py-3 rounded-xl">
                            <div class="text-xl mb-1">⚙️</div>
                            <div class="text-white font-semibold text-sm">Settings</div>
                            <div class="text-gray-400 text-xs mt-1">Bot configuration</div>
                        </div>
                    </div>
                </div>

                <!-- Customer's Data -->
                <div class="bg-dark-100 rounded-xl p-4 border border-green-500/30">
                    <h4 class="text-green-400 font-semibold mb-4 text-center">Customer Se Juda Data</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="bg-dark-200 p-3 rounded-lg text-center">
                            <span class="text-xl">💬</span>
                            <div class="text-white text-sm mt-1">Chats</div>
                            <div class="text-gray-500 text-xs">Messages history</div>
                        </div>
                        <div class="bg-dark-200 p-3 rounded-lg text-center">
                            <span class="text-xl">📋</span>
                            <div class="text-white text-sm mt-1">Leads</div>
                            <div class="text-gray-500 text-xs">Inquiry info</div>
                        </div>
                        <div class="bg-dark-200 p-3 rounded-lg text-center">
                            <span class="text-xl">📦</span>
                            <div class="text-white text-sm mt-1">Products</div>
                            <div class="text-gray-500 text-xs">Interested items</div>
                        </div>
                        <div class="bg-dark-200 p-3 rounded-lg text-center">
                            <span class="text-xl">⏰</span>
                            <div class="text-white text-sm mt-1">Followups</div>
                            <div class="text-gray-500 text-xs">Scheduled msgs</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Navigation -->
        <div class="glass-card p-6 rounded-2xl">
            <h3 class="text-lg font-bold text-white mb-4">Quick Jump</h3>
            <div class="flex flex-wrap gap-3">
                <a href="#overall-flow"
                    class="px-4 py-2 bg-blue-500/20 border border-blue-500/50 text-blue-300 rounded-lg hover:bg-blue-500/30 transition-colors text-sm">
                    System Flow
                </a>
                <a href="#admin-sections"
                    class="px-4 py-2 bg-purple-500/20 border border-purple-500/50 text-purple-300 rounded-lg hover:bg-purple-500/30 transition-colors text-sm">
                    Admin Sections
                </a>
                <a href="#chat-flow"
                    class="px-4 py-2 bg-green-500/20 border border-green-500/50 text-green-300 rounded-lg hover:bg-green-500/30 transition-colors text-sm">
                    Chat Flow
                </a>
                <a href="#lead-management"
                    class="px-4 py-2 bg-orange-500/20 border border-orange-500/50 text-orange-300 rounded-lg hover:bg-orange-500/30 transition-colors text-sm">
                    Lead Management
                </a>
                <a href="#ai-integration"
                    class="px-4 py-2 bg-indigo-500/20 border border-indigo-500/50 text-indigo-300 rounded-lg hover:bg-indigo-500/30 transition-colors text-sm">
                    AI Integration
                </a>
                <a href="#data-relationships"
                    class="px-4 py-2 bg-pink-500/20 border border-pink-500/50 text-pink-300 rounded-lg hover:bg-pink-500/30 transition-colors text-sm">
                    Data Relations
                </a>
            </div>
        </div>
    </div>

    <style>
        .flow-box {
            transition: all 0.3s ease;
        }

        .flow-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .section-card:hover {
            transform: translateY(-3px);
        }

        .db-box {
            transition: all 0.3s ease;
        }

        .db-box:hover {
            transform: scale(1.05);
        }

        html {
            scroll-behavior: smooth;
        }
    </style>
@endsection