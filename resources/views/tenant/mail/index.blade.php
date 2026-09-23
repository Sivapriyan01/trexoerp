@extends('layouts.tenant')
@section('title', 'Mail Client')
@section('page-title', 'Mail')

@section('content')
<div class="h-[calc(100vh-120px)] flex gap-4 overflow-hidden" x-data="{ 
    folder: '{{ $folder }}', 
    activeAccount: '{{ $accounts->first()->id ?? 0 }}',
    activeMessage: null,
    composing: {{ request('compose_to') ? 'true' : 'false' }},
    managingAccounts: {{ $errors->any() ? 'true' : 'false' }},
    syncing: false,
    async selectMessage(msg) {
        this.activeMessage = msg;
    },
    async toggleStar(id) {
        const res = await fetch(`/mail/toggle-star/${id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        if (res.ok) {
            if (this.activeMessage && this.activeMessage.id == id) {
                this.activeMessage.is_starred = !this.activeMessage.is_starred;
            }
        }
    },
    async syncInbox() {
        if (this.syncing || this.managingAccounts || this.composing) return;
        console.log('Syncing inbox in background...');
        this.syncing = true;
        try {
            const res = await fetch('{{ route('tenant.mail.sync') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            
            if (res.status === 401) {
                // Session expired, stop auto-syncing
                console.warn('Session expired. Auto-sync stopped.');
                return;
            }

            const data = await res.json();
            if (data.success) {
                location.reload();
            }
        } catch (e) {
            console.error('Auto-sync failed:', e.message);
        } finally {
            this.syncing = false;
        }
    }
}" x-init="
    @if(request('compose_to'))
        $nextTick(() => { document.getElementById('to_input').value = '{{ request('compose_to') }}'; });
    @endif
    
    // Auto-sync every 30 seconds
    setInterval(() => {
        if (!composing && !syncing && !managingAccounts) {
            syncInbox();
        }
    }, 30000);
">
    <!-- COLUMN 0: Account Switcher (Extreme Left) -->
    <div class="w-16 flex flex-col items-center py-6 gap-4 bg-slate-900/5 dark:bg-white/5 rounded-[2.5rem] backdrop-blur-md">
        @foreach($accounts as $acc)
            <button @click="activeAccount = '{{ $acc->id }}'" 
                    :class="activeAccount == '{{ $acc->id }}' ? 'ring-2 ring-blue-600 ring-offset-2 dark:ring-offset-slate-900' : 'opacity-60 hover:opacity-100'"
                    class="w-10 h-10 rounded-2xl bg-white dark:bg-slate-800 shadow-sm flex items-center justify-center font-black text-xs text-slate-700 dark:text-white transition-all uppercase"
                    title="{{ $acc->email }}">
                {{ substr($acc->account_name, 0, 1) }}
            </button>
        @endforeach
        <button @click="managingAccounts = true" class="w-10 h-10 rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-700 flex items-center justify-center text-slate-400 hover:text-blue-600 hover:border-blue-600 transition-all">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
        </button>
    </div>

    <!-- LEFT COLUMN: Folders -->
    <div class="w-52 glass-card rounded-[2.5rem] flex flex-col p-4">
        <button @click="composing = true" class="w-full py-3 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 shadow-lg shadow-blue-100 transition-all mb-6 flex items-center justify-center gap-2">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
            Compose
        </button>

        <nav class="space-y-1">
            <a href="?folder=inbox" :class="folder == 'inbox' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'" class="flex items-center justify-between px-4 py-3 rounded-xl transition-all group">
                <div class="flex items-center gap-3">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0l-8 4-8-4m16 0l-8 4-8-4"/></svg>
                    <span class="text-[10px] font-black uppercase">Inbox</span>
                </div>
                @if($stats['unread'] > 0)
                    <span class="px-1.5 py-0.5 bg-blue-600 text-white text-[8px] font-black rounded-full">{{ $stats['unread'] }}</span>
                @endif
            </a>
            <a href="?folder=starred" :class="folder == 'starred' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'" class="flex items-center justify-between px-4 py-3 rounded-xl transition-all group">
                <div class="flex items-center gap-3">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.382-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    <span class="text-[10px] font-black uppercase">Starred</span>
                </div>
                @if($stats['starred'] > 0)
                    <span class="px-1.5 py-0.5 bg-amber-400 text-white text-[8px] font-black rounded-full">{{ $stats['starred'] }}</span>
                @endif
            </a>
            <a href="?folder=sent" :class="folder == 'sent' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                <span class="text-[10px] font-black uppercase">Sent</span>
            </a>
            <button @click="managingAccounts = true" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="text-[10px] font-black uppercase">Accounts</span>
            </button>
        </nav>

        <div class="mt-auto pt-4 border-t border-slate-50 text-center">
            <p class="text-[8px] font-black text-slate-300 uppercase tracking-widest">Auto-sync active</p>
        </div>
    </div>

    <!-- MIDDLE COLUMN: Message List -->
    <div class="w-80 glass-card rounded-[2.5rem] flex flex-col overflow-hidden">
        <div class="p-6 border-b border-slate-50 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-[11px] font-black text-slate-900 uppercase tracking-widest">{{ ucfirst($folder) }}</h3>
            </div>
            <form class="relative">
                <input type="hidden" name="folder" value="{{ $folder }}">
                <input type="text" name="q" value="{{ is_string($search) ? $search : '' }}" placeholder="Search messages..." 
                       class="w-full pl-8 pr-4 py-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold outline-none focus:ring-2 focus:ring-blue-100 transition-all">
                <svg width="12" height="12" class="absolute left-3 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </form>
        </div>
        <div class="flex-1 overflow-y-auto custom-scrollbar">
            @forelse($messages as $msg)
                <div @click="selectMessage({{ json_encode($msg) }})" 
                     :class="activeMessage && activeMessage.id == {{ $msg->id }} ? 'bg-blue-50/50 dark:bg-blue-900/20 border-blue-100 dark:border-blue-800' : 'hover:bg-slate-50 dark:hover:bg-slate-800/50 border-transparent'"
                     class="p-5 border-b border-slate-50 dark:border-slate-800 last:border-0 cursor-pointer transition-all border-l-4 group"
                     style="border-left-color: {{ isset($msg->is_read) && !$msg->is_read ? '#4f46e5' : 'transparent' }}">
                    <div class="flex justify-between items-start mb-1">
                        <p class="text-[10px] font-black text-slate-900 dark:text-white truncate pr-2">{{ $msg->sender_name }}</p>
                        <div class="flex items-center gap-2">
                            <button @click.stop="toggleStar({{ $msg->id }}); {{ $msg->is_starred ? '$el.classList.remove(\'text-amber-400\'); $el.classList.add(\'text-slate-200\')' : '$el.classList.add(\'text-amber-400\'); $el.classList.remove(\'text-slate-200\')' }}" 
                                    class="{{ $msg->is_starred ? 'text-amber-400' : 'text-slate-200 group-hover:text-slate-300' }} transition-colors">
                                <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </button>
                            <p class="text-[8px] font-bold text-slate-400 whitespace-nowrap">{{ \Carbon\Carbon::parse($msg->created_at)->diffForHumans() }}</p>
                        </div>
                    </div>
                    <p class="text-[10px] font-black text-slate-600 mb-1 line-clamp-1">{{ $msg->subject }}</p>
                    <p class="text-[9px] font-bold text-slate-400 line-clamp-2 leading-relaxed">
                        {{ strip_tags($msg->message) }}
                    </p>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center h-full p-12 opacity-20 text-center">
                    <svg width="48" height="48" class="mb-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0l-8 4-8-4m16 0l-8 4-8-4"/></svg>
                    <p class="text-[10px] font-black uppercase tracking-widest">No messages found</p>
                    <p class="text-[8px] font-bold mt-2 uppercase">Click 'Sync Inbox' to fetch real emails</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- RIGHT COLUMN: Message View -->
    <div class="flex-1 glass-card rounded-[2.5rem] flex flex-col overflow-hidden">
        <template x-if="activeMessage">
            <div class="h-full flex flex-col animate-in fade-in zoom-in-95 duration-300">
                <div class="p-8 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-black text-slate-900 dark:text-white tracking-tight" x-text="activeMessage.subject"></h2>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-[8px] font-black rounded uppercase" x-text="folder"></span>
                            <span class="text-[9px] font-bold text-slate-400" x-text="new Date(activeMessage.created_at).toLocaleString()"></span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button @click="toggleStar(activeMessage.id)" class="p-2 transition-colors rounded-xl" :class="activeMessage.is_starred ? 'text-amber-400 bg-amber-50 dark:bg-amber-900/20' : 'text-slate-400 hover:text-slate-600 bg-slate-50 dark:bg-slate-800'">
                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </button>
                        <button class="p-2 text-slate-400 hover:text-rose-500 rounded-xl transition-colors bg-slate-50 dark:bg-slate-800">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
                
                <div class="p-8 border-b border-slate-50 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-black text-lg uppercase shadow-sm" x-text="activeMessage.sender_name.charAt(0)"></div>
                    <div>
                        <p class="text-sm font-black text-slate-900" x-text="activeMessage.sender_name"></p>
                        <p class="text-[10px] font-bold text-slate-400" x-text="activeMessage.sender_email"></p>
                    </div>
                </div>

                <div class="flex-1 p-10 overflow-y-auto custom-scrollbar text-sm font-medium text-slate-600 leading-relaxed bg-slate-50/20" x-html="activeMessage.message">
                </div>

                <div class="p-6 border-t border-slate-50 flex justify-end gap-3 bg-white/50 backdrop-blur-sm">
                    <button @click="composing = true; $nextTick(() => { document.getElementById('to_input').value = activeMessage.sender_email; document.getElementById('subject_input').value = 'Re: ' + activeMessage.subject; })" class="px-6 py-2 border border-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all">Reply</button>
                    <button class="px-6 py-2 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition-all">Forward</button>
                </div>
            </div>
        </template>
        <template x-if="!activeMessage">
            <div class="h-full flex flex-col items-center justify-center text-center p-20 opacity-20">
                <div class="w-32 h-32 rounded-full border-4 border-dashed border-slate-300 flex items-center justify-center mb-6">
                    <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-xl font-black text-slate-900 mb-2 uppercase tracking-tight">No Message Selected</h3>
                <p class="text-[11px] font-bold text-slate-500 max-w-xs uppercase">Choose a message from the list to view its contents and take action.</p>
            </div>
        </template>
    </div>

    <!-- COMPOSE MODAL -->
    <div x-show="composing" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
        <div class="glass-card w-full max-w-2xl rounded-[3rem] overflow-hidden animate-in fade-in zoom-in-95 duration-300 shadow-2xl">
            <div class="p-8 border-b border-slate-50 flex items-center justify-between">
                <h3 class="text-lg font-black text-slate-900 tracking-tight">New Message</h3>
                <button @click="composing = false" class="p-2 text-slate-400 hover:text-slate-600 transition-colors">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="compose_form" class="p-8 space-y-4">
                @csrf
                <input type="hidden" name="account_id" :value="activeAccount">
                <div class="space-y-1">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest px-1">Recipient</label>
                    <input type="email" name="to" id="to_input" required placeholder="customer@example.com" class="w-full px-6 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-xs font-bold outline-none focus:ring-4 focus:ring-blue-100 transition-all">
                </div>
                <div class="space-y-1">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest px-1">Subject</label>
                    <input type="text" name="subject" id="subject_input" required placeholder="How can we help you?" class="w-full px-6 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-xs font-bold outline-none focus:ring-4 focus:ring-blue-100 transition-all">
                </div>
                <div class="space-y-1">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest px-1">Message</label>
                    <textarea name="message" id="message_input" required rows="8" placeholder="Type your message here..." class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-[2rem] text-xs font-bold outline-none focus:ring-4 focus:ring-blue-100 transition-all resize-none"></textarea>
                </div>
                <div class="flex justify-end pt-4 gap-3">
                    <button type="button" @click="composing = false" class="px-8 py-3 text-slate-500 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 rounded-xl transition-all">Cancel</button>
                    <button type="submit" id="send_btn" class="px-8 py-3 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 shadow-lg shadow-blue-100 transition-all active:scale-95">Send Email</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MANAGE ACCOUNTS MODAL -->
    <div x-show="managingAccounts" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
        <div class="glass-card w-full max-w-3xl rounded-[2.5rem] overflow-hidden animate-in fade-in zoom-in-95 duration-300 shadow-2xl flex h-[550px]">
            <!-- Sidebar for Modal -->
            <div class="w-52 border-r border-slate-50 p-6 bg-slate-50/30 flex flex-col">
                <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-widest mb-6">Accounts</h3>
                <div class="space-y-2 flex-1 overflow-y-auto custom-scrollbar pr-2">
                    @foreach($accounts as $acc)
                        <div class="group flex items-center justify-between p-2.5 bg-white rounded-xl shadow-sm border border-slate-100 transition-all">
                            <div class="min-w-0">
                                <p class="text-[9px] font-black text-slate-900 truncate">{{ $acc->account_name }}</p>
                                <p class="text-[8px] font-bold text-slate-400 truncate">{{ $acc->email }}</p>
                            </div>
                            <form action="{{ route('tenant.mail.accounts.delete', $acc->id) }}" method="POST" onsubmit="return confirm('Delete this account?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1 text-rose-300 hover:text-rose-500 opacity-0 group-hover:opacity-100 transition-all">
                                    <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
                <button @click="managingAccounts = false" class="mt-4 w-full py-2 text-slate-500 text-[9px] font-black uppercase tracking-widest border-t border-slate-100">Close</button>
            </div>

            <!-- Content Area for Modal -->
            <div class="flex-1 p-8 overflow-y-auto custom-scrollbar">
                @if (session('success'))
                    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-100 rounded-xl flex items-center gap-3">
                        <svg width="14" height="14" class="text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        <p class="text-[9px] font-black text-emerald-600 uppercase tracking-widest">{{ session('success') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 p-3 bg-rose-50 border border-rose-100 rounded-xl">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li class="text-[9px] font-black text-rose-600 uppercase tracking-widest">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <h3 class="text-base font-black text-slate-900 tracking-tight mb-1">Add Account</h3>
                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-6">Enter SMTP/IMAP settings.</p>

                <form action="{{ route('tenant.mail.accounts.store') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <!-- Basic Info -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Name</label>
                            <input type="text" name="account_name" required placeholder="Sales Team" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold outline-none focus:ring-4 focus:ring-blue-100 transition-all">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Email</label>
                            <input type="email" name="email" required placeholder="shop@gmail.com" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold outline-none focus:ring-4 focus:ring-blue-100 transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <!-- Outgoing (SMTP) -->
                        <div class="space-y-4">
                            <h4 class="text-[8px] font-black text-blue-600 uppercase tracking-widest border-b border-blue-50 pb-1.5">Outgoing (SMTP)</h4>
                            <div class="space-y-3">
                                <div class="space-y-1">
                                    <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Host</label>
                                    <input type="text" name="smtp_host" placeholder="smtp.gmail.com" class="w-full px-4 py-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold outline-none focus:ring-2 focus:ring-blue-100 transition-all">
                                </div>
                                <div class="flex gap-2">
                                    <div class="w-20 space-y-1">
                                        <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Port</label>
                                        <input type="number" name="smtp_port" placeholder="587" class="w-full px-4 py-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold outline-none focus:ring-2 focus:ring-blue-100 transition-all">
                                    </div>
                                    <div class="flex-1 space-y-1">
                                        <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Security</label>
                                        <select name="smtp_encryption" class="w-full px-4 py-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold outline-none focus:ring-2 focus:ring-blue-100 transition-all">
                                            <option value="tls">TLS</option>
                                            <option value="ssl">SSL</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Username</label>
                                    <input type="text" name="smtp_user" class="w-full px-4 py-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold outline-none focus:ring-2 focus:ring-blue-100 transition-all">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Password</label>
                                    <input type="password" name="smtp_password" class="w-full px-4 py-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold outline-none focus:ring-2 focus:ring-blue-100 transition-all">
                                </div>
                            </div>
                        </div>

                        <!-- Incoming (IMAP) -->
                        <div class="space-y-4">
                            <h4 class="text-[8px] font-black text-emerald-600 uppercase tracking-widest border-b border-emerald-50 pb-1.5">Incoming (IMAP)</h4>
                            <div class="space-y-3">
                                <div class="space-y-1">
                                    <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Host</label>
                                    <input type="text" name="imap_host" placeholder="imap.gmail.com" class="w-full px-4 py-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold outline-none focus:ring-2 focus:ring-emerald-100 transition-all">
                                </div>
                                <div class="flex gap-2">
                                    <div class="w-20 space-y-1">
                                        <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Port</label>
                                        <input type="number" name="imap_port" placeholder="993" class="w-full px-4 py-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold outline-none focus:ring-2 focus:ring-emerald-100 transition-all">
                                    </div>
                                    <div class="flex-1 space-y-1">
                                        <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Security</label>
                                        <select name="imap_encryption" class="w-full px-4 py-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold outline-none focus:ring-2 focus:ring-emerald-100 transition-all">
                                            <option value="ssl">SSL</option>
                                            <option value="tls">TLS</option>
                                        </select>
                                    </div>
                                </div>
                                <p class="text-[7px] font-bold text-slate-400 leading-tight pt-2 uppercase tracking-tighter">
                                    Enable IMAP in your provider settings (e.g. Gmail Settings).
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-50">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" name="is_default" value="1" class="w-4 h-4 rounded border-slate-200 text-blue-600">
                            <span class="text-[8px] font-black text-slate-600 uppercase tracking-widest">Set Default</span>
                        </label>
                        <button type="submit" class="px-8 py-3 bg-slate-900 text-white rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-slate-800 transition-all">Save Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('compose_form').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('send_btn');
    const originalText = btn.innerText;
    
    btn.innerText = "Sending...";
    btn.disabled = true;

    const formData = new FormData(this);
    
    fetch('{{ route('tenant.mail.broadcast') }}', { 
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        if (response.status === 401) {
            alert("Your session has expired. Please refresh the page and log in again.");
            return;
        }
        return response.json();
    })
    .then(data => {
        if(!data) return;
        if(data.success) {
            alert("Email sent successfully from: " + data.sent_from);
            location.reload();
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("An unexpected error occurred.");
    })
    .finally(() => {
        btn.innerText = originalText;
        btn.disabled = false;
    });
});
</script>
@endpush

<style>
[x-cloak] { display: none !important; }
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(0,0,0,0.05);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(0,0,0,0.1);
}
</style>

