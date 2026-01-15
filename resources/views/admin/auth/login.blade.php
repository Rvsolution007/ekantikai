<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - ChatBot SaaS</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: { 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca' },
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
            background: linear-gradient(135deg, #0f0f1a 0%, #1a1a2e 50%, #16213e 100%);
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
            background: #6366f1;
            top: -100px;
            left: -100px;
        }

        .shape-2 {
            width: 300px;
            height: 300px;
            background: #a855f7;
            bottom: -50px;
            right: -50px;
            animation-delay: -5s;
        }

        .shape-3 {
            width: 200px;
            height: 200px;
            background: #ec4899;
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
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2);
            outline: none;
        }

        .btn-gradient {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
            background-size: 200% 200%;
            animation: gradient-shift 3s ease infinite;
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(99, 102, 241, 0.4);
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
                box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
            }

            50% {
                box-shadow: 0 0 40px rgba(99, 102, 241, 0.8);
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
                class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center mx-auto mb-4 logo-glow">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Welcome Back</h1>
            <p class="text-gray-400">Sign in to your account</p>
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

            <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-6">
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
                            placeholder="you@example.com">
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
                            class="w-4 h-4 rounded border-gray-600 bg-dark-200 text-primary-500 focus:ring-primary-500">
                        <span class="text-sm text-gray-400">Remember me</span>
                    </label>
                    <a href="#" class="text-sm text-primary-400 hover:text-primary-300 transition-colors">
                        Forgot password?
                    </a>
                </div>

                <button type="submit" class="btn-gradient w-full py-4 rounded-xl text-white font-semibold text-lg">
                    Sign In
                </button>
            </form>

            <!-- Divider -->
            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-700"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-dark-100 text-gray-500">or continue with</span>
                </div>
            </div>

            <!-- Social Login -->
            <div class="grid grid-cols-2 gap-4">
                <button
                    class="flex items-center justify-center space-x-2 py-3 rounded-xl border border-gray-700 hover:bg-white/5 transition-colors">
                    <svg class="w-5 h-5 text-white" viewBox="0 0 24 24">
                        <path fill="currentColor"
                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                        <path fill="currentColor"
                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                        <path fill="currentColor"
                            d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                        <path fill="currentColor"
                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                    </svg>
                    <span class="text-gray-300 text-sm">Google</span>
                </button>
                <button
                    class="flex items-center justify-center space-x-2 py-3 rounded-xl border border-gray-700 hover:bg-white/5 transition-colors">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />
                    </svg>
                    <span class="text-gray-300 text-sm">GitHub</span>
                </button>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-gray-500 text-sm mt-8">
            Don't have an account? <a href="#" class="text-primary-400 hover:text-primary-300">Contact admin</a>
        </p>
    </div>
</body>

</html>