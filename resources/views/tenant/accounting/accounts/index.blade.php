@extends('layouts.tenant')

@section('page-title', 'Dashboard')

@section('content')
<div x-data="{ 
    showNewAccountModal: false,
    showEditModal: false,
    editAccount: { id: null, name: '', code: '', type: 'Asset', icon: '' },
    openEdit(account) {
        this.editAccount = JSON.parse(JSON.stringify(account));
        this.showEditModal = true;
    }
}" class="space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Chart of Accounts</h1>
        </div>
        <div>
            <button @click="showNewAccountModal = true" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-sm transition-all flex items-center gap-2">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                New Account
            </button>
        </div>
    </div>

    <!-- Accounts Table -->
    <div class="bg-white dark:bg-slate-900/50 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Icon</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Code</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Name</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Type</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">System</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($accounts as $account)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="px-6 py-4">
                            @if($account->icon)
                            <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500">
                                <i class="{{ $account->icon }} text-lg"></i>
                                @if(strlen($account->icon) <= 2) {{ $account->icon }} @endif
                            </div>
                            @else
                            <div class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700 flex items-center justify-center text-slate-400">-</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-slate-500 font-medium">{{ $account->code }}</span>
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                            {{ $account->name }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-slate-600 dark:text-slate-300">{{ $account->type }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-slate-400">{{ $account->system }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button @click="openEdit({{ $account->toJson() }})" class="text-blue-600 hover:text-blue-700 font-bold transition-colors">Edit</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500">No accounts found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- New Account Modal -->
    <div x-show="showNewAccountModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center">
        <!-- Backdrop -->
        <div x-show="showNewAccountModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showNewAccountModal = false"
             class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>

        <!-- Modal Content -->
        <div x-show="showNewAccountModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 scale-95"
             class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-[2rem] shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden mx-4">
            
            <form action="{{ route('tenant.accounting.accounts.store') }}" method="POST">
                @csrf
                <div class="p-8">
                    <h2 class="text-xl font-black text-slate-900 dark:text-white mb-6">New Account</h2>

                    <div class="space-y-5">
                        <div class="space-y-1.5">
                            <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Name</label>
                            <input type="text" name="name" required
                                class="w-full bg-slate-50 dark:bg-slate-800/50 border border-blue-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-500/10 focus:border-blue-400 outline-none transition-all">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Code</label>
                            <input type="text" name="code"
                                class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-500/10 focus:border-blue-400 outline-none transition-all">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Type</label>
                            <div class="relative">
                                <select name="type" required
                                    class="w-full appearance-none bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl pl-4 pr-10 py-2.5 font-medium focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-500/10 focus:border-blue-400 outline-none transition-all cursor-pointer">
                                    <option value="Asset">Asset</option>
                                    <option value="Liability">Liability</option>
                                    <option value="Equity">Equity</option>
                                    <option value="Revenue">Revenue</option>
                                    <option value="Expense">Expense</option>
                                </select>
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <div x-data="{ iconVal: '' }" class="space-y-1.5">
                            <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Icon</label>
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 bg-slate-100 dark:bg-slate-800 rounded-xl flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-700">
                                    <i :class="iconVal" class="text-xl text-slate-500" x-show="iconVal && iconVal.length > 2"></i>
                                    <span x-text="iconVal" x-show="iconVal && iconVal.length <= 2"></span>
                                    <svg x-show="!iconVal" width="20" height="20" class="text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <input type="text" name="icon" x-model="iconVal" placeholder="e.g. bx bx-wallet or 💸"
                                    class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-500/10 focus:border-blue-400 outline-none transition-all">
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1">Enter a Boxicons class (e.g., bx bx-wallet) or an emoji.</p>
                        </div>
                    </div>
                </div>
                
                <div class="px-8 py-5 bg-slate-50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                    <button type="button" @click="showNewAccountModal = false"
                        class="px-5 py-2.5 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-sm transition-all">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Account Modal -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center">
        <!-- Backdrop -->
        <div x-show="showEditModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showEditModal = false"
             class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>

        <!-- Modal Content -->
        <div x-show="showEditModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 scale-95"
             class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-[2rem] shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden mx-4">
            
            <form :action="'/accounting/accounts/' + editAccount.id" method="POST">
                @csrf
                @method('PUT')
                <div class="p-8">
                    <h2 class="text-xl font-black text-slate-900 dark:text-white mb-6">Edit Account</h2>

                    <div class="space-y-5">
                        <div class="space-y-1.5">
                            <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Name</label>
                            <input type="text" name="name" x-model="editAccount.name" required
                                class="w-full bg-slate-50 dark:bg-slate-800/50 border border-blue-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-500/10 focus:border-blue-400 outline-none transition-all">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Code</label>
                            <input type="text" name="code" x-model="editAccount.code"
                                class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-500/10 focus:border-blue-400 outline-none transition-all">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Type</label>
                            <div class="relative">
                                <select name="type" x-model="editAccount.type" required
                                    class="w-full appearance-none bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl pl-4 pr-10 py-2.5 font-medium focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-500/10 focus:border-blue-400 outline-none transition-all cursor-pointer">
                                    <option value="Asset">Asset</option>
                                    <option value="Liability">Liability</option>
                                    <option value="Equity">Equity</option>
                                    <option value="Revenue">Revenue</option>
                                    <option value="Expense">Expense</option>
                                </select>
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Icon</label>
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 bg-slate-100 dark:bg-slate-800 rounded-xl flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-700">
                                    <i :class="editAccount.icon" class="text-xl text-slate-500" x-show="editAccount.icon && editAccount.icon.length > 2"></i>
                                    <span x-text="editAccount.icon" x-show="editAccount.icon && editAccount.icon.length <= 2"></span>
                                    <svg x-show="!editAccount.icon" width="20" height="20" class="text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <input type="text" name="icon" x-model="editAccount.icon" placeholder="e.g. bx bx-wallet or 💸"
                                    class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-500/10 focus:border-blue-400 outline-none transition-all">
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1">Enter a Boxicons class (e.g., bx bx-wallet) or an emoji.</p>
                        </div>
                    </div>
                </div>
                
                <div class="px-8 py-5 bg-slate-50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                    <button type="button" @click="showEditModal = false"
                        class="px-5 py-2.5 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-sm transition-all">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
