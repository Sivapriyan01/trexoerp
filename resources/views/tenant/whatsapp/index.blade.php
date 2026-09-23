@extends('layouts.tenant')
@section('title', 'WhatsApp Marketing')
@section('page-title', 'Communication Hub')

@section('content')
<div class="space-y-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="glass-card rounded-3xl p-5 border border-slate-100 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 group hover:-translate-y-1 transition-all duration-300">
            <h4 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Total Contacts</h4>
            <p class="text-xl font-black text-slate-900 dark:text-white">{{ number_format($customers->total()) }}</p>
            <div class="mt-3 w-full h-1 bg-blue-100 dark:bg-blue-950/40 rounded-full overflow-hidden">
                <div class="h-full bg-blue-500 w-full"></div>
            </div>
        </div>
        <div class="glass-card rounded-3xl p-5 border border-slate-100 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 group hover:-translate-y-1 transition-all duration-300">
            <h4 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Sent Today</h4>
            <p class="text-xl font-black text-slate-900 dark:text-white">124</p>
            <div class="mt-3 w-full h-1 bg-green-100 dark:bg-green-950/40 rounded-full overflow-hidden">
                <div class="h-full bg-green-500 w-1/3"></div>
            </div>
        </div>
        <div class="glass-card rounded-3xl p-5 border border-slate-100 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 group hover:-translate-y-1 transition-all duration-300">
            <h4 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Success Rate</h4>
            <p class="text-xl font-black text-slate-900 dark:text-white">98.2%</p>
            <div class="mt-3 w-full h-1 bg-emerald-100 dark:bg-emerald-950/40 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 w-[98%]"></div>
            </div>
        </div>
        <div class="glass-card rounded-3xl p-5 border border-slate-100 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 group hover:-translate-y-1 transition-all duration-300">
            <h4 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Pending</h4>
            <p class="text-xl font-black text-slate-900 dark:text-white">0</p>
            <div class="mt-3 w-full h-1 bg-amber-100 dark:bg-amber-950/40 rounded-full overflow-hidden">
                <div class="h-full bg-amber-500 w-0"></div>
            </div>
        </div>
    </div>

    <!-- Actions & Search -->
    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative flex-1 md:w-80">
                <input type="text" id="customer_search" placeholder="Search customer name or phone..." 
                       class="w-full pl-10 pr-4 py-3 bg-white dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800/80 rounded-2xl text-[11px] font-bold text-slate-800 dark:text-slate-200 outline-none focus:ring-4 focus:ring-blue-50 dark:focus:ring-slate-800 shadow-sm transition-all placeholder:text-slate-400 dark:placeholder:text-slate-600">
                <svg class="absolute left-3.5 top-3.5 text-slate-300 dark:text-slate-600" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="openBulkModal()" class="flex items-center gap-2 px-6 py-3.5 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.1em] hover:bg-blue-700 transition-all shadow-xl shadow-blue-100 dark:shadow-none">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                Send Broadcast
            </button>
        </div>
    </div>

    <!-- Customer Table -->
    <div class="glass-card rounded-[2.5rem] border border-slate-100 dark:border-slate-800/80 shadow-xl overflow-hidden bg-white dark:bg-slate-900/40">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-50 dark:border-slate-800/60 bg-slate-50/20 dark:bg-slate-950/30">
                        <th class="px-6 py-5 text-[9px] font-black text-slate-400 uppercase tracking-widest">Customer</th>
                        <th class="px-4 py-5 text-[9px] font-black text-slate-400 uppercase tracking-widest">Phone Number</th>
                        <th class="px-4 py-5 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                        <th class="px-4 py-5 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/40" id="customer_table_body">
                    @foreach($customers as $customer)
                        @php
                            $displayName = $customer->name ?: 'Unknown Customer';
                            $displayInitial = substr(preg_replace('/[^A-Za-z0-9]/', '', $displayName) ?: 'U', 0, 1);
                        @endphp
                        <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-950/20 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-50 to-white dark:from-slate-800 dark:to-slate-900 text-blue-600 dark:text-blue-400 flex items-center justify-center text-[11px] font-black shadow-sm border border-blue-50 dark:border-slate-800">
                                        {{ strtoupper($displayInitial) }}
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-black text-slate-900 dark:text-slate-200 uppercase leading-tight">{{ $displayName }}</p>
                                        <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500">Regular Customer</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-950/50 px-3 py-1 rounded-lg">{{ $customer->phone }}</span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 text-[8px] font-black uppercase tracking-widest">Active</span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <button onclick="openMessageModal('{{ $customer->phone }}', '{{ $displayName }}')" 
                                        class="p-2.5 bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 rounded-xl hover:bg-green-600 dark:hover:bg-green-600 hover:text-white transition-all shadow-sm">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 448 512"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.7 17.7 68.9 27.1 106.1 27.1h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-5.5-2.8-23.2-8.5-44.2-27.1-16.4-14.6-27.4-32.7-30.6-38.2-3.2-5.6-.3-8.6 2.5-11.3 2.5-2.5 5.6-6.5 8.3-9.8 2.8-3.3 3.7-5.6 5.6-9.3 1.9-3.7.9-6.9-.5-9.8-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 13.2 5.8 23.5 9.2 31.5 11.8 13.3 4.2 25.4 3.6 35 2.2 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-50 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-950/30">
            {{ $customers->links() }}
        </div>
    </div>
</div>

{{-- Messaging Modal --}}
<div id="message_modal" class="hidden fixed inset-0 bg-slate-900/80 backdrop-blur-md z-[100] flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-[3rem] shadow-2xl w-full max-w-lg transform transition-all overflow-hidden">
        <div class="p-8 pb-4 flex items-center justify-between">
            <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-widest" id="modal_title">Send Message</h3>
            <button onclick="closeModal('message_modal')" class="p-2 text-slate-300 hover:text-slate-600 transition-colors">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <div class="p-8 pt-4 space-y-6">
            <div id="modal_customer_info" class="p-4 bg-slate-50 dark:bg-slate-950/50 rounded-2xl border border-slate-100 dark:border-slate-800">
                <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase mb-1">Recipient</p>
                <p class="text-xs font-black text-slate-900 dark:text-slate-200 uppercase" id="target_name"></p>
                <p class="text-[10px] font-bold text-blue-600 dark:text-blue-400" id="target_phone_display"></p>
                <input type="hidden" id="target_phone">
            </div>

            <div class="space-y-1">
                <label class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase px-1">Quick Templates</label>
                <div class="grid grid-cols-3 gap-2">
                    <button onclick="applyTemplate('welcome')" class="p-2 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 text-slate-800 dark:text-slate-200 rounded-xl text-[9px] font-black uppercase hover:border-blue-200 dark:hover:border-blue-800 transition-all">Welcome</button>
                    <button onclick="applyTemplate('payment')" class="p-2 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 text-slate-800 dark:text-slate-200 rounded-xl text-[9px] font-black uppercase hover:border-blue-200 dark:hover:border-blue-800 transition-all">Payment</button>
                    <button onclick="applyTemplate('festive')" class="p-2 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 text-slate-800 dark:text-slate-200 rounded-xl text-[9px] font-black uppercase hover:border-blue-200 dark:hover:border-blue-800 transition-all">Festive</button>
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase px-1">Message</label>
                <textarea id="target_message" rows="5" class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800 rounded-[2rem] text-xs font-bold text-slate-800 dark:text-slate-200 outline-none focus:ring-4 focus:ring-green-50 dark:focus:ring-slate-850 transition-all placeholder:text-slate-400 dark:placeholder:text-slate-600" placeholder="Enter message..."></textarea>
            </div>

            <div id="bulk_progress" class="hidden space-y-2">
                <div class="flex justify-between text-[10px] font-black uppercase">
                    <span class="text-slate-400 dark:text-slate-500">Broadcasting...</span>
                    <span id="progress_text" class="text-blue-600 dark:text-blue-400">0%</span>
                </div>
                <div class="w-full h-2 bg-slate-100 dark:bg-slate-950/50 rounded-full overflow-hidden">
                    <div id="progress_bar" class="h-full bg-blue-600 transition-all duration-500" style="width: 0%"></div>
                </div>
            </div>

            <button id="send_btn" onclick="sendWhatsApp()" class="w-full py-4 bg-green-600 text-white rounded-[1.5rem] text-xs font-black uppercase tracking-[0.1em] shadow-lg shadow-green-100 dark:shadow-none hover:scale-[1.02] transition-all">
                Send Message
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let isBulk = false;
    const templates = {
        welcome: "Hello! Welcome to our store. We're happy to have you as a valued customer! 🛍️",
        payment: "Hi! This is a gentle reminder regarding your pending payment. Kindly settle at your earliest convenience. 🙏",
        festive: "Season's Greetings! ✨ Visit us today for amazing festive offers! 🎊"
    };

    function openMessageModal(phone, name) {
        isBulk = false;
        document.getElementById('modal_title').innerText = 'Send Message';
        document.getElementById('modal_customer_info').classList.remove('hidden');
        document.getElementById('target_name').innerText = name;
        document.getElementById('target_phone_display').innerText = phone;
        document.getElementById('target_phone').value = phone;
        document.getElementById('send_btn').innerText = 'Send Message';
        document.getElementById('message_modal').classList.remove('hidden');
    }

    function openBulkModal() {
        isBulk = true;
        document.getElementById('modal_title').innerText = 'Broadcast to All';
        document.getElementById('modal_customer_info').classList.add('hidden');
        document.getElementById('send_btn').innerText = 'Start Broadcast';
        document.getElementById('message_modal').classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.getElementById('bulk_progress').classList.add('hidden');
    }

    function applyTemplate(key) {
        document.getElementById('target_message').value = templates[key];
    }

    async function sendWhatsApp() {
        const msg = document.getElementById('target_message').value;
        if (!msg) return alert('Please enter a message');

        if (isBulk) {
            await startBulkBroadcast(msg);
        } else {
            await sendSingleMessage(document.getElementById('target_phone').value, msg);
        }
    }

    async function sendSingleMessage(phone, msg) {
        const btn = document.getElementById('send_btn');
        btn.disabled = true;
        btn.innerText = 'Sending...';

        try {
            const res = await fetch(`{{ route('tenant.whatsapp.send') }}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ phone, message: msg })
            });
            const result = await res.json();
            if (result.success) {
                alert('✅ Sent!');
                closeModal('message_modal');
            } else {
                alert('❌ ' + result.message);
            }
        } catch (e) { alert('Failed'); }
        btn.disabled = false;
        btn.innerText = 'Send Message';
    }

    async function startBulkBroadcast(msg) {
        if (!confirm('Broadcast to all customers?')) return;
        
        const progressDiv = document.getElementById('bulk_progress');
        const progressBar = document.getElementById('progress_bar');
        const progressText = document.getElementById('progress_text');
        const btn = document.getElementById('send_btn');

        // Fetch all phones first
        progressDiv.classList.remove('hidden');
        progressText.innerText = 'Fetching contacts...';
        btn.disabled = true;

        const rows = Array.from(document.querySelectorAll('#customer_table_body tr'));
        const total = rows.length;
        let success = 0;
        let failed = 0;

        for (let i = 0; i < total; i++) {
            const row = rows[i];
            const phone = row.cells[1].innerText.trim();
            const name = row.cells[0].innerText.trim();

            progressText.innerText = `Sending to ${name} (${i + 1}/${total})`;
            const percent = Math.round(((i + 1) / total) * 100);
            progressBar.style.width = percent + '%';

            try {
                const res = await fetch(`{{ route('tenant.whatsapp.send') }}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ phone, message: msg })
                });
                const result = await res.json();
                if (result.success) success++; else failed++;
            } catch (e) { failed++; }
        }

        progressText.innerText = `Finished! Success: ${success}, Failed: ${failed}`;
        alert(`✅ Broadcast complete!\nSuccess: ${success}\nFailed: ${failed}`);
        btn.disabled = false;
        setTimeout(() => progressDiv.classList.add('hidden'), 5000);
    }

    // Live Search
    document.getElementById('customer_search').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#customer_table_body tr');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    });
</script>
@endpush
