<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $tenant?->id ?? 'Store' }} - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSS Fallback & Vite -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

<div class="w-full max-w-md bg-white shadow-lg rounded-2xl p-8">

    <!-- Header -->
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            {{ $tenant?->id ?? 'Demo Store' }}
        </h1>
        <p class="text-sm text-gray-500">Billing & Inventory Management</p>
    </div>

    <!-- Welcome -->
    <div class="text-center mb-6">
        <h2 class="text-xl font-semibold">Welcome back</h2>
        <p class="text-sm text-gray-500">Sign in to continue to your store</p>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('tenant.login.post') }}">
        @csrf

        <!-- Email -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-600">
                Email / Username
            </label>
            <input type="text" name="email"
                   value="{{ old('email') }}"
                   placeholder="you@store.com"
                   required
                   class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring focus:ring-blue-200">
            @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-600">
                Password
            </label>
            <input type="password" name="password"
                   required
                   placeholder="••••••••"
                   class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring focus:ring-blue-200">
            @error('password')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember -->
        <div class="flex items-center justify-between mb-4">
            <label class="flex items-center text-sm">
                <input type="checkbox" name="remember" class="mr-2">
                Keep me signed in
            </label>
        </div>

        <!-- Button -->
        <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
            Sign in
        </button>
    </form>

    <!-- Footer -->
    <div class="text-center mt-6 text-sm text-gray-500">
        <p>Having trouble signing in? Contact support</p>
        <p class="mt-2 text-xs">Powered by TrexoERP</p>
    </div>

</div>

</body>
</html>
