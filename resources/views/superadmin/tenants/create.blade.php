<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Tenant') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl">
                <div class="p-8 bg-white border-b border-gray-200">
                    
                    <form method="POST" action="{{ route('superadmin.tenants.store') }}" class="space-y-6">
                        @csrf

                        <!-- Tenant ID (Slug) -->
                        <div>
                            <label for="id" class="block text-sm font-medium text-gray-700">Tenant Identifier (ID)</label>
                            <input type="text" name="id" id="id" value="{{ old('id') }}" required placeholder="e.g. kalyani-traders"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <p class="mt-1 text-xs text-gray-500">The unique slug/ID for the tenant database.</p>
                            @error('id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Store Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Store / Business Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="e.g. Kalyani Traders"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Admin Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="admin@example.com"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Admin Password</label>
                                <input type="password" name="password" id="password" required placeholder="Enter password"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Domain -->
                            <div>
                                <label for="domain" class="block text-sm font-medium text-gray-700">Domain Name</label>
                                <input type="text" name="domain" id="domain" value="{{ old('domain') }}" required placeholder="e.g. kalyani.localhost"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">Do not include http://</p>
                                @error('domain') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Plan -->
                            <div>
                                <label for="plan" class="block text-sm font-medium text-gray-700">Subscription Plan</label>
                                <select name="plan" id="plan" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="basic">Basic</option>
                                    <option value="standard" selected>Standard</option>
                                    <option value="premium">Premium</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-4 border-t border-gray-100">
                            <a href="{{ route('superadmin.tenants.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Create Tenant Database
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
