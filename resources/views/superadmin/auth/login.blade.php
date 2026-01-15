<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Super Admin Login - ChatBot SaaS</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: { 500: '#ef4444', 600: '#dc2626', 700: '#b91c1c' },
                        dark: { 100: '#1e1e2d', 200: '#1a1a27', 300: '#151521' }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        .glass {
            background: rgba(30, 30, 45, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .gradient-bg {
            background: linear-gradient(135deg, #1a0f0f 0%, #2d1a1a 50%, #1a1016 100%);
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.5;
            animation: float 15s infinite ease-in-out;
        }

        .shape-1 {
            width: 400px;
            height: 400px;
            background: #ef4444;
            top: -100px;
            left: -100px;
        }

        .shape-2 {
            width: 300px;
            height: 300px;
            background: #f97316;
            bottom: -50px;
            right: -50px;
            animation-delay: -5s;
        }

        .shape-3 {
            width: 200px;
            height: 200px;
            background: #dc2626;
            top: 50%;
            left: 50%;
            animation-delay: -10s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            25% {
                transform: translate(30px, -30px) rotate(5deg);
            }

            50% {
                transform: translate(-20px, 20px) rotate(-5deg);
            }

            75% {
                transform: translate(10px, -10px) rotate(3deg);
            }
        }

        .input-modern {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .input-modern:focus {
            border-color: #ef4444;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.2);
            outline: none;
        }

        .btn-gradient {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 50%, #b91c1c 100%);
            background-size: 200% 200%;
            animation: gradient-shift 3s ease infinite;
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(239, 68, 68, 0.4);
        }

        @keyframes gradient-shift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .logo-glow {
            animation: pulse-glow 2s infinite;
        }

        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(239, 68, 68, 0.4);
            }

            50% {
                box-shadow: 0 0 40px rgba(239, 68, 68, 0.8);
            }
        }
    </style>
</head>

<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <!-- Floating Shapes -->
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <!-- Login Card -->
    <div class="relative z-10 w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div
                class="w-16 h-16 rounded-2xl bg-gradient-to-br from-red-500 to-orange-600 flex items-center justify-center mx-auto mb-4 logo-glow">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Super Admin Portal</h1>
            <p class="text-gray-400">Authorized personnel only</p>
        </div>

        <!-- Form Card -->
        <div class="glass rounded-3xl p-8">
            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="text-red-400 text-sm">{{ $errors->first() }}</span>
                    </div>
                </div>
            @endif

            <form action="{{ route('superadmin.login.submit') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Email Address</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </span>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="input-modern w-full pl-12 pr-4 py-4 rounded-xl text-white placeholder-gray-500"
                            placeholder="superadmin@example.com">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </span>
                        <input type="password" name="password" required
                            class="input-modern w-full pl-12 pr-4 py-4 rounded-xl text-white placeholder-gray-500"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="remember"
                            class="w-4 h-4 rounded border-gray-600 bg-dark-200 text-red-500 focus:ring-red-500">
                        <span class="text-sm text-gray-400">Remember me</span>
                    </label>
                </div>

                <button type="submit" class="btn-gradient w-full py-4 rounded-xl text-white font-semibold text-lg">
                    Access Super Admin
                </button>
            </form>
        </div>

        <!-- Footer -->
        <p class="text-center text-gray-500 text-sm mt-8">
            Regular Admin? <a href="{{ route('admin.login') }}" class="text-red-400 hover:text-red-300">Login here</a>
        </p>
    </div>
</body>

</html>