<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - ChatBot</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: { 50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc', 400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca', 800: '#3730a3', 900: '#312e81' },
                        dark: { 100: '#1e1e2d', 200: '#1a1a27', 300: '#151521', 400: '#12121a', 500: '#0d0d14' }
                    }
                }
            }
        }
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        [x-cloak] {
            display: none !important;
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #1e1e2d;
        }

        ::-webkit-scrollbar-thumb {
            background: #6366f1;
            border-radius: 3px;
        }

        .glass {
            background: rgba(30, 30, 45, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .glass-light {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .gradient-text {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 50%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar-link {
            transition: all 0.3s ease;
        }

        .sidebar-link:hover {
            background: rgba(99, 102, 241, 0.15);
            transform: translateX(5px);
        }

        .sidebar-link.active {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.3) 0%, transparent 100%);
            border-left: 3px solid #6366f1;
        }

        .stat-card {
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
        }

        .input-dark {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .input-dark:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
            outline: none;
        }

        .table-row {
            transition: all 0.2s ease;
        }

        .table-row:hover {
            background: rgba(99, 102, 241, 0.05);
        }

        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
            }

            50% {
                box-shadow: 0 0 40px rgba(99, 102, 241, 0.6);
            }
        }

        .animate-pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }

        /* Glass Card */
        .glass-card {
            background: rgba(30, 30, 45, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* ========================================
           DARK THEME FOR ALL FORM ELEMENTS
           ======================================== */

        /* Text Inputs */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"],
        input[type="url"],
        input[type="number"],
        input[type="search"],
        input[type="date"],
        input[type="datetime-local"],
        input[type="time"],
        textarea {
            background: rgba(30, 41, 59, 0.8) !important;
            border: 1px solid rgba(148, 163, 184, 0.2) !important;
            color: #fff !important;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            width: 100%;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="tel"]:focus,
        input[type="url"]:focus,
        input[type="number"]:focus,
        input[type="search"]:focus,
        input[type="date"]:focus,
        input[type="datetime-local"]:focus,
        input[type="time"]:focus,
        textarea:focus {
            outline: none !important;
            border-color: rgba(139, 92, 246, 0.5) !important;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1) !important;
        }

        input::placeholder,
        textarea::placeholder {
            color: rgba(148, 163, 184, 0.5) !important;
        }

        /* Date inputs dark scheme */
        input[type="date"],
        input[type="datetime-local"],
        input[type="time"] {
            color-scheme: dark;
        }

        /* Select Dropdowns */
        select {
            background: rgba(30, 41, 59, 0.8) !important;
            border: 1px solid rgba(148, 163, 184, 0.2) !important;
            color: #fff !important;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            width: 100%;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
            background-position: right 0.5rem center !important;
            background-repeat: no-repeat !important;
            background-size: 1.5em 1.5em !important;
            padding-right: 2.5rem;
        }

        select:focus {
            outline: none !important;
            border-color: rgba(139, 92, 246, 0.5) !important;
        }

        select option {
            background: #1e293b !important;
            color: #fff !important;
            padding: 0.5rem;
        }

        /* Buttons */
        .btn-gradient {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.4);
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead {
            background: rgba(255, 255, 255, 0.05);
        }

        table th {
            color: #9ca3af;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1rem 1.5rem;
            text-align: left;
        }

        table td {
            padding: 1rem 1.5rem;
            color: #fff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        table tbody tr:hover {
            background: rgba(99, 102, 241, 0.05);
        }

        /* Modal/Popup Dark Theme */
        .modal-overlay {
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background: rgba(30, 30, 45, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
        }

        /* Cards */
        .card {
            background: rgba(30, 30, 45, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
        }

        /* Labels */
        label {
            color: #d1d5db;
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* Pagination */
        .pagination {
            display: flex;
            gap: 0.5rem;
        }

        .pagination a,
        .pagination span {
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.2);
            color: #fff;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background: rgba(99, 102, 241, 0.3);
            border-color: rgba(99, 102, 241, 0.5);
        }

        .pagination .active span {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-color: transparent;
        }

        /* File Inputs */
        input[type="file"] {
            background: rgba(30, 41, 59, 0.8) !important;
            border: 1px dashed rgba(148, 163, 184, 0.3) !important;
            color: #fff !important;
            padding: 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
        }

        input[type="file"]::file-selector-button {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border: none;
            color: #fff;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            cursor: pointer;
            margin-right: 1rem;
        }

        /* Checkbox and Radio */
        input[type="checkbox"],
        input[type="radio"] {
            width: 1.25rem !important;
            height: 1.25rem !important;
            background: rgba(30, 41, 59, 0.8) !important;
            border: 2px solid rgba(148, 163, 184, 0.3) !important;
            border-radius: 0.25rem;
            cursor: pointer;
        }

        input[type="checkbox"]:checked,
        input[type="radio"]:checked {
            background: #6366f1 !important;
            border-color: #6366f1 !important;
        }

        /* Alert/Notice boxes */
        .alert {
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 0.5rem;
            padding: 1rem;
            color: #fff;
        }

        .alert-success {
            border-left: 4px solid #22c55e;
        }

        .alert-error {
            border-left: 4px solid #ef4444;
        }

        .alert-warning {
            border-left: 4px solid #f59e0b;
        }

        /* Dropdown menus */
        .dropdown-menu {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 0.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .dropdown-item {
            color: #fff;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background: rgba(99, 102, 241, 0.2);
        }

        /* Remove any white backgrounds */
        .bg-white {
            background: rgba(30, 30, 45, 0.7) !important;
        }

        /* Force dark on common elements */
        dialog,
        [role="dialog"],
        .modal,
        .popup {
            background: rgba(30, 30, 45, 0.95) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-dark-300 text-gray-100 min-h-screen">
    <div x-data="{ sidebarOpen: true }" class="flex h-screen overflow-hidden">

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'w-64' : 'w-20'"
            class="hidden lg:flex flex-col fixed inset-y-0 left-0 z-50 glass transition-all duration-300">
            <!-- Logo -->
            <div class="flex items-center justify-between h-16 px-4 border-b border-white/10">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center animate-pulse-glow">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <span x-show="sidebarOpen" class="text-xl font-bold gradient-text">ChatBot</span>
                </a>
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <!-- User Info -->
            <div x-show="sidebarOpen" class="px-4 py-3">
                <div class="glass-light rounded-xl p-3 flex items-center space-x-3">
                    <div
                        class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-400 to-teal-500 flex items-center justify-center">
                        <span
                            class="text-white text-sm font-medium">{{ substr(auth()->guard('admin')->user()->name ?? 'U', 0, 1) }}</span>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-sm font-semibold text-white truncate">
                            {{ auth()->guard('admin')->user()->name ?? 'User' }}
                        </p>
                        <p class="text-xs text-gray-400">Tenant</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                <p x-show="sidebarOpen" class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                    Main</p>

                <a href="{{ route('admin.dashboard') }}"
                    class="sidebar-link flex items-center space-x-3 px-3 py-2.5 rounded-xl text-gray-300 hover:text-white {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z" />
                    </svg>
                    <span x-show="sidebarOpen">Dashboard</span>
                </a>

                <a href="{{ route('admin.leads.index') }}"
                    class="sidebar-link flex items-center space-x-3 px-3 py-2.5 rounded-xl text-gray-300 hover:text-white {{ request()->routeIs('admin.leads.*') && !request()->routeIs('admin.lead-status.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span x-show="sidebarOpen">Leads</span>
                </a>

                <a href="{{ route('admin.lead-status.index') }}"
                    class="sidebar-link flex items-center space-x-3 px-3 py-2.5 rounded-xl text-gray-300 hover:text-white {{ request()->routeIs('admin.lead-status.*') ? 'active' : '' }} ml-4">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7" />
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm">Lead Statuses</span>
                </a>

                <a href="{{ route('admin.clients.index') }}"
                    class="sidebar-link flex items-center space-x-3 px-3 py-2.5 rounded-xl text-gray-300 hover:text-white {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span x-show="sidebarOpen">Clients</span>
                </a>

                <a href="{{ route('admin.users.index') }}"
                    class="sidebar-link flex items-center space-x-3 px-3 py-2.5 rounded-xl text-gray-300 hover:text-white {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span x-show="sidebarOpen">Users</span>
                </a>

                <a href="{{ route('admin.chats.index') }}"
                    class="sidebar-link flex items-center space-x-3 px-3 py-2.5 rounded-xl text-gray-300 hover:text-white {{ request()->routeIs('admin.chats.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <span x-show="sidebarOpen">Chats</span>
                </a>

                <a href="{{ route('admin.catalogue.index') }}"
                    class="sidebar-link flex items-center space-x-3 px-3 py-2.5 rounded-xl text-gray-300 hover:text-white {{ request()->routeIs('admin.catalogue.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span x-show="sidebarOpen">Catalogue</span>
                </a>

                <a href="{{ route('admin.followups.index') }}"
                    class="sidebar-link flex items-center space-x-3 px-3 py-2.5 rounded-xl text-gray-300 hover:text-white {{ request()->routeIs('admin.followups.*') && !request()->routeIs('admin.followup-templates.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span x-show="sidebarOpen">Followups</span>
                </a>

                <a href="{{ route('admin.followup-templates.index') }}"
                    class="sidebar-link flex items-center space-x-3 px-3 py-2.5 rounded-xl text-gray-300 hover:text-white {{ request()->routeIs('admin.followup-templates.*') ? 'active' : '' }} ml-4">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span x-show="sidebarOpen" class="text-sm">Templates</span>
                </a>

                <a href="{{ route('admin.credits.index') }}"
                    class="sidebar-link flex items-center space-x-3 px-3 py-2.5 rounded-xl text-gray-300 hover:text-white {{ request()->routeIs('admin.credits.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1" />
                    </svg>
                    <span x-show="sidebarOpen">Credits</span>
                </a>

                <a href="{{ route('admin.workflow.index') }}"
                    class="sidebar-link flex items-center space-x-3 px-3 py-2.5 rounded-xl text-gray-300 hover:text-white {{ request()->routeIs('admin.workflow.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span x-show="sidebarOpen">Workflow</span>
                </a>


                <p x-show="sidebarOpen"
                    class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-6 mb-2">Settings</p>

                <a href="{{ route('admin.settings.index') }}"
                    class="sidebar-link flex items-center space-x-3 px-3 py-2.5 rounded-xl text-gray-300 hover:text-white {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span x-show="sidebarOpen">Settings</span>
                </a>
            </nav>

            <!-- Logout -->
            <div class="p-4 border-t border-white/10">
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-xl text-red-400 hover:bg-red-500/10 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span x-show="sidebarOpen">Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main :class="sidebarOpen ? 'lg:ml-64' : 'lg:ml-20'" class="flex-1 overflow-y-auto transition-all duration-300">
            <!-- Top Bar -->
            <header class="sticky top-0 z-40 glass h-16 flex items-center justify-between px-6">
                <h1 class="text-xl font-semibold text-white">@yield('page-title', 'Dashboard')</h1>
                <div class="flex items-center space-x-4">
                    <div class="hidden md:block relative">
                        <input type="text" placeholder="Search..."
                            class="input-dark w-64 pl-10 pr-4 py-2 rounded-xl text-sm text-white placeholder-gray-500">
                        <svg class="w-5 h-5 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <button class="relative p-2 text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                </div>
            </header>

            <div class="p-6">
                @if(session('success'))
                    <div class="mb-6 glass-light border-l-4 border-green-500 p-4 rounded-r-xl">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="text-green-400">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 glass-light border-l-4 border-red-500 p-4 rounded-r-xl">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="text-red-400">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="mb-6 glass-light border-l-4 border-yellow-500 p-4 rounded-r-xl">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="text-yellow-400">{{ session('warning') }}</span>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 glass-light border-l-4 border-red-500 p-4 rounded-r-xl">
                        <div class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-red-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                            <div class="text-red-400">
                                <ul class="list-disc list-inside">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif


                @yield('content')
            </div>
        </main>
    </div>
    @stack('scripts')
</body>

</html>