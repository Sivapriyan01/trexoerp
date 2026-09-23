{{-- resources/views/tenant/partials/stock_health_modal.blade.php --}}
@if(isset($stockHealth))
<div id="stock_health_overlay" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
    <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-xs transform transition-all duration-500 scale-95 opacity-0 overflow-hidden" id="stock_health_content">
        <!-- Header -->
        <div class="relative p-6 pb-2 text-center">
            <div class="absolute top-4 right-4">
                <button onclick="closeStockHealth()" class="p-1.5 text-slate-300 hover:text-slate-600 transition-colors">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-md">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            
            <h2 class="text-sm font-black text-slate-900 uppercase tracking-tight">Stock Health</h2>
        </div>

        <!-- Metrics Grid -->
        <div class="px-6 py-4">
            <div class="grid grid-cols-2 gap-3">
                <div class="p-4 rounded-2xl bg-blue-50/50 border border-blue-100 flex flex-col items-center justify-center gap-1">
                    <p class="text-[8px] font-black uppercase tracking-widest opacity-60">Total</p>
                    <p class="text-xl font-black text-blue-700">{{ $stockHealth['total'] }}</p>
                </div>
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-100 flex flex-col items-center justify-center gap-1">
                    <p class="text-[8px] font-black uppercase tracking-widest opacity-60">Low</p>
                    <p class="text-xl font-black text-amber-600">{{ $stockHealth['low'] }}</p>
                </div>
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-100 flex flex-col items-center justify-center gap-1">
                    <p class="text-[8px] font-black uppercase tracking-widest opacity-60">Out</p>
                    <p class="text-xl font-black text-rose-600">{{ $stockHealth['out'] }}</p>
                </div>
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 flex flex-col items-center justify-center gap-1">
                    <p class="text-[8px] font-black uppercase tracking-widest opacity-60">Health</p>
                    <p class="text-xl font-black text-emerald-600">{{ $stockHealth['percentage'] }}%</p>
                </div>
            </div>
            @if(isset($stockHealth['expired']))
            <div class="mt-3 p-4 rounded-2xl bg-rose-50 border border-rose-100 flex flex-col items-center justify-center gap-1 animate-pulse">
                <p class="text-[8px] font-black uppercase tracking-widest text-rose-500">Expired Items</p>
                <p class="text-xl font-black text-rose-600">{{ $stockHealth['expired'] }}</p>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="p-6 pt-2 flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="dont_show_today" class="w-4 h-4 rounded border-slate-200 text-blue-600">
                    <label for="dont_show_today" class="text-[8px] font-black text-slate-400 uppercase tracking-widest cursor-pointer">Hide today</label>
                </div>
                <span id="health_countdown" class="text-[8px] font-black text-slate-400 uppercase tracking-widest">30s</span>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <button onclick="closeStockHealth()" class="py-2.5 bg-slate-50 text-slate-500 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-100 transition-all">
                    Close
                </button>
                <a href="{{ route('tenant.inventory.index') }}" class="py-2.5 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest text-center shadow-lg shadow-blue-100 transition-all">
                    Detail
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    let countdownInterval;
    let secondsLeft = 30;

    function showStockHealth(force = false) {
        const lastShown = localStorage.getItem('stock_health_last_shown');
        const today = new Date().toDateString();
        
        if (!force && lastShown === today) return;

        const overlay = document.getElementById('stock_health_overlay');
        const content = document.getElementById('stock_health_content');
        
        overlay.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);

        startCountdown();
    }

    function startCountdown() {
        const display = document.getElementById('health_countdown');
        countdownInterval = setInterval(() => {
            secondsLeft--;
            display.textContent = `${secondsLeft}s`;
            if (secondsLeft <= 0) closeStockHealth();
        }, 1000);
    }

    function closeStockHealth() {
        // Always save to localStorage when closed to prevent repetitive popups in the same session/day
        localStorage.setItem('stock_health_last_shown', new Date().toDateString());
        
        const overlay = document.getElementById('stock_health_overlay');
        const content = document.getElementById('stock_health_content');
        
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            overlay.classList.add('hidden');
            clearInterval(countdownInterval);
        }, 400);
    }

    window.onload = () => {
        // Only show automatically on the MAIN dashboard, exactly
        const path = window.location.pathname;
        if (path === '/dashboard' || path === '/') {
            // Force show if session has success message (just logged in)
            const isLogin = {{ session()->has('success') ? 'true' : 'false' }};
            setTimeout(() => showStockHealth(isLogin), 1000);
        }
    };
</script>
@endif

