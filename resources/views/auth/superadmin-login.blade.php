<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Super Admin Login — TrexoERP</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-mesh min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md relative">
    <!-- Decorative Elements -->
    <div class="absolute -top-20 -left-20 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-20 -right-20 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl"></div>

    <!-- Logo Section -->
    <div class="text-center mb-10 relative">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-tr from-indigo-600 to-blue-500 rounded-3xl mb-6 shadow-2xl shadow-indigo-200 animate-bounce-slow">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
        </div>

        <h1 class="text-4xl font-bold text-slate-900 tracking-tight">TrexoERP</h1>
        <p class="text-indigo-600 font-medium mt-2">Super Admin Portal</p>
    </div>

    <!-- Login Card -->
    <div class="glass rounded-[2.5rem] p-10 relative overflow-hidden">
        <div class="relative z-10">
            <h2 class="text-2xl font-bold text-slate-800 mb-2">Welcome Back</h2>
            <p class="text-slate-500 mb-8 text-sm">Please enter your credentials to access the central hub.</p>

            <!-- Status Messages -->
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50/50 border border-red-100 rounded-2xl">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm text-red-600 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                            {{ $error }}
                        </p>
                    @endforeach
                </div>
            @endif

            @if (session('status'))
                <div class="mb-6 p-4 bg-emerald-50/50 border border-emerald-100 rounded-2xl text-sm text-emerald-600">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('superadmin.login.post') }}" class="space-y-6">
                @csrf

                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-slate-700 ml-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="admin@trexoerp.com"
                           class="input-premium">
                </div>

                <div class="space-y-1.5">
                    <div class="flex justify-between items-center px-1">
                        <label class="block text-sm font-semibold text-slate-700">Password</label>
                    </div>
                    <input type="password" name="password" required
                           placeholder="••••••••"
                           class="input-premium">
                </div>

                <div class="flex items-center justify-between px-1">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <div class="relative flex items-center">
                            <input type="checkbox" name="remember" class="peer appearance-none w-5 h-5 border-2 border-slate-200 rounded-md checked:bg-indigo-600 checked:border-indigo-600 transition-all">
                            <svg class="absolute w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 ml-0.5 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-slate-600 group-hover:text-indigo-600 transition-colors">Keep me signed in</span>
                    </label>
                </div>

                <button type="submit" class="w-full btn-premium py-4 text-base shadow-xl shadow-indigo-100">
                    Sign in to Portal
                </button>
            </form>
        </div>

        <!-- Decorative background circle -->
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-32 h-32 bg-indigo-50 rounded-full opacity-50"></div>
    </div>

    <!-- Footer Information -->
    <div class="mt-8 text-center space-y-4">
        <p class="text-xs text-slate-400">
            Secure connection established. All actions are logged.
        </p>
        <div class="h-px w-12 bg-slate-200 mx-auto"></div>
        <p class="text-xs font-medium text-slate-500 uppercase tracking-widest">
            &copy; {{ date('Y') }} TrexoERP Systems
        </p>
    </div>
</div>

<style>
    @keyframes bounce-slow {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    .animate-bounce-slow {
        animation: bounce-slow 4s ease-in-out infinite;
    }
</style>

</body>
</html>