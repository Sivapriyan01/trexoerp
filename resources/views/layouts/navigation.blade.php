<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <!-- Left -->
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ tenant() ? route('tenant.dashboard') : route('superadmin.dashboard') }}" class="flex items-center">
                        <img src="/assets/images/trex-logo-light.png" alt="Trex ERP Logo" class="h-8 w-auto object-contain dark:hidden">
                        <img src="/assets/images/trex-logo.png" alt="Trex ERP Logo" class="h-8 w-auto object-contain hidden dark:block">
                    </a>
                </div>

                <!-- Links -->
                <div class="hidden space-x-8 sm:ms-10 sm:flex">
                    @if(tenant())
                        <x-nav-link 
                            :href="route('tenant.dashboard')" 
                            :active="request()->routeIs('tenant.dashboard')">
                            Dashboard
                        </x-nav-link>
                        <x-nav-link 
                            :href="route('tenant.users.index')" 
                            :active="request()->routeIs('tenant.users.*')">
                            Users
                        </x-nav-link>
                    @else
                        <x-nav-link 
                            :href="route('superadmin.dashboard')" 
                            :active="request()->routeIs('superadmin.dashboard')">
                            Dashboard
                        </x-nav-link>
                        <x-nav-link 
                            :href="route('superadmin.tenants.index')" 
                            :active="request()->routeIs('superadmin.tenants.*')">
                            Tenants
                        </x-nav-link>
                        <x-nav-link 
                            :href="route('superadmin.settings.index')" 
                            :active="request()->routeIs('superadmin.settings.*')">
                            Settings
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Right -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-6">
                <!-- Theme/Palette -->
                <button class="text-gray-500 hover:text-gray-700 transition">
                    <svg width="24" height="24" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-3M9.707 3.293a1 1 0 00-1.414 0l-6 6a1 1 0 000 1.414l2 2a1 1 0 001.414 0L12 7.414l-2.293-2.293z" />
                    </svg>
                </button>

                <!-- Notifications -->
                <div class="relative group">
                    <button class="text-gray-500 hover:text-gray-700 transition">
                        <svg width="24" height="24" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-bold px-1.5 rounded-full border-2 border-white">11</span>
                    </button>
                </div>

                <!-- User Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none transition">
                            <svg width="24" height="24" class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ Auth::user()->name }}</span>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Logout -->
                        <form method="POST" action="{{ tenant() ? route('tenant.logout') : route('superadmin.logout') }}">
                            @csrf
                            <x-dropdown-link 
                                :href="tenant() ? route('tenant.logout') : route('superadmin.logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Logout
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

        </div>
    </div>

</nav>
