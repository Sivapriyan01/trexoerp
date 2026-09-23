@extends('layouts.tenant')
@section('title', 'Daily Expense')
@section('content')
<div x-data="dailyExpense()" class="flex gap-4 animate-in fade-in duration-500">

  {{-- ====== LEFT PANEL ====== --}}
  <div class="w-80 shrink-0 space-y-4">

    {{-- Form Card --}}
    <div class="glass-card p-6 rounded-3xl space-y-4">
      <div class="flex gap-2 items-center">
        <input type="date" x-model="form.expense_date" class="flex-1 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-900 dark:text-white font-bold focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" />
        <button @click="pettyCashOpen=true" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-colors shadow-lg shadow-purple-500/10 dark:shadow-none">₹ Add Petty Cash</button>
      </div>
      <div>
        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Category</label>
        <select x-model="form.category" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm mt-1 text-slate-900 dark:text-white font-bold focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
          <option value="" class="dark:bg-slate-800">Select</option>
          @foreach($expenseCategories as $cat)<option value="{{ $cat }}" class="dark:bg-slate-800">{{ $cat }}</option>@endforeach
        </select>
      </div>
      <div>
        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Description</label>
        <select x-model="form.description" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm mt-1 text-slate-900 dark:text-white font-bold focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
          <option value="" class="dark:bg-slate-800">Select</option>
          @foreach($descriptions as $d)<option value="{{ $d->name }}" class="dark:bg-slate-800">{{ $d->name }}</option>@endforeach
        </select>
      </div>
      <div>
        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Payment Mode</label>
        <select x-model="form.payment_mode" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm mt-1 text-slate-900 dark:text-white font-bold focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
          <option class="dark:bg-slate-800">Cash</option><option class="dark:bg-slate-800">Bank</option><option class="dark:bg-slate-800">UPI</option>
        </select>
      </div>
      <div>
        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Amount</label>
        <input type="number" x-model="form.amount" step="0.01" min="0" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm mt-1 text-slate-900 dark:text-white font-bold focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" placeholder="0.00" />
      </div>
      <button @click="addExpense" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl text-sm font-bold transition-colors shadow-lg shadow-blue-500/10 dark:shadow-none">Add Expense</button>
    </div>

    {{-- Manage Descriptions --}}
    <div class="glass-card p-6 rounded-3xl">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-black uppercase text-slate-700 dark:text-slate-300 tracking-widest">Manage Descriptions</h3>
        <button @click="descModalOpen=true" class="text-xs text-blue-600 dark:text-blue-400 font-bold hover:underline">+ Add</button>
      </div>
      <div class="space-y-1 max-h-64 overflow-y-auto custom-scrollbar">
        <template x-for="d in descs" :key="d.id">
          <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800/50 group">
            <span x-show="editDescId!==d.id" class="text-sm font-semibold text-slate-700 dark:text-slate-300" x-text="d.name"></span>
            <input x-show="editDescId===d.id" x-model="editDescName" @keyup.enter="saveDesc" class="text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 flex-1 mr-2 text-slate-900 dark:text-white font-bold focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" />
            <div class="flex gap-2 items-center">
              <button x-show="editDescId!==d.id" @click="startEditDesc(d)" class="text-blue-500 hover:text-blue-700 opacity-0 group-hover:opacity-100 transition-opacity">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              </button>
              <button x-show="editDescId===d.id" @click="saveDesc" class="text-green-600 text-xs font-bold px-1">Save</button>
              <button @click="deleteDesc(d.id)" class="text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-opacity">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              </button>
            </div>
          </div>
        </template>
        <p x-show="descs.length===0" class="text-xs text-slate-400 dark:text-slate-500 text-center py-4">No descriptions yet</p>
      </div>
    </div>
  </div>

  {{-- ====== RIGHT PANEL ====== --}}
  <div class="flex-1 glass-card p-6 rounded-3xl">

    {{-- Summary & Controls --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800/50">
      <div class="flex flex-wrap gap-4 text-sm">
        <span class="text-slate-500 dark:text-slate-400">Total Expenses: <strong class="text-slate-800 dark:text-white font-black">₹{{ number_format($totalExpenses,2) }}</strong></span>
        <span class="text-slate-500 dark:text-slate-400">Total Inwards: <strong class="text-slate-800 dark:text-white font-black">₹{{ number_format($totalInwards,2) }}</strong></span>
        <span class="{{ $netBalance >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-black">Net Balance: ₹{{ number_format($netBalance,2) }}</span>
      </div>
      <div class="flex gap-2 items-center">
        <form method="GET" id="period-form">
          <select name="period" onchange="document.getElementById('period-form').submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white rounded-xl px-3 py-2 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
            @foreach(['today'=>'Today','yesterday'=>'Yesterday','this_week'=>'This Week','this_month'=>'This Month','last_month'=>'Last Month'] as $val=>$label)
              <option value="{{ $val }}" {{ $period===$val ? 'selected' : '' }} class="dark:bg-slate-800">{{ $label }}</option>
            @endforeach
          </select>
        </form>
        <a href="{{ route('tenant.daily-expense.export', ['period'=>$period]) }}"
           class="flex items-center gap-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 px-3 py-2 rounded-xl text-xs font-bold transition-colors">
          <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
          Export
        </a>
      </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-0 mb-5 border-b border-slate-100 dark:border-slate-800">
      <button @click="tab='expense'" :class="tab==='expense' ? 'border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-400' : 'text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300'" class="px-5 py-2.5 text-sm font-bold transition-colors">Expense Report</button>
      <button @click="tab='petty'"   :class="tab==='petty'   ? 'border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-400' : 'text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300'" class="px-5 py-2.5 text-sm font-bold transition-colors">Petty Cash Report</button>
      <button @click="tab='balance'" :class="tab==='balance' ? 'border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-400' : 'text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300'" class="px-5 py-2.5 text-sm font-bold transition-colors">Balance Report</button>
    </div>

    {{-- Expense Report --}}
    <div x-show="tab==='expense'">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">
              <th class="py-3 text-left px-2">SL NO</th>
              <th class="py-3 text-left px-2">Date</th>
              <th class="py-3 text-left px-2">Payment Mode</th>
              <th class="py-3 text-left px-2">Description</th>
              <th class="py-3 text-left px-2">Category</th>
              <th class="py-3 text-right px-2">Amount</th>
              <th class="py-3 text-center px-2">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($expenses as $i => $e)
            <tr class="border-b border-slate-50 dark:border-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
              <td class="py-3 px-2 text-slate-500 dark:text-slate-400">{{ $i+1 }}</td>
              <td class="py-3 px-2 font-semibold text-slate-800 dark:text-slate-200">{{ $e->expense_date->format('d-m-Y') }}</td>
              <td class="py-3 px-2"><span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 rounded-lg text-xs font-bold">{{ $e->payment_mode }}</span></td>
              <td class="py-3 px-2 text-slate-700 dark:text-slate-300">{{ $e->description ?? '—' }}</td>
              <td class="py-3 px-2 text-slate-500 dark:text-slate-400">{{ $e->category ?? '—' }}</td>
              <td class="py-3 px-2 text-right font-black text-slate-800 dark:text-white">₹{{ number_format($e->amount,2) }}</td>
              <td class="py-3 px-2 text-center">
                <button onclick="deleteEntry({{ $e->id }})" class="text-red-400 hover:text-red-600 transition-colors">
                  <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
              </td>
            </tr>
            @empty
            <tr><td colspan="7" class="py-12 text-center text-slate-400 dark:text-slate-500 text-xs font-bold uppercase">No expense data found for selected period</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Petty Cash Report --}}
    <div x-show="tab==='petty'" x-cloak>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">
              <th class="py-3 text-left px-2">SL NO</th>
              <th class="py-3 text-left px-2">Date</th>
              <th class="py-3 text-left px-2">Source</th>
              <th class="py-3 text-left px-2">Mode</th>
              <th class="py-3 text-right px-2">Amount</th>
              <th class="py-3 text-center px-2">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($pettyCash as $i => $p)
            <tr class="border-b border-slate-50 dark:border-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
              <td class="py-3 px-2 text-slate-500 dark:text-slate-400">{{ $i+1 }}</td>
              <td class="py-3 px-2 font-semibold text-slate-800 dark:text-slate-200">{{ $p->expense_date->format('d-m-Y') }}</td>
              <td class="py-3 px-2 text-slate-700 dark:text-slate-300">{{ $p->source ?? '—' }}</td>
              <td class="py-3 px-2"><span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 rounded-lg text-xs font-bold">{{ $p->payment_mode }}</span></td>
              <td class="py-3 px-2 text-right font-black text-green-600 dark:text-green-400">₹{{ number_format($p->amount,2) }}</td>
              <td class="py-3 px-2 text-center">
                <button onclick="deleteEntry({{ $p->id }})" class="text-red-400 hover:text-red-600 transition-colors">
                  <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
              </td>
            </tr>
            @empty
            <tr><td colspan="6" class="py-12 text-center text-slate-400 dark:text-slate-500 text-xs font-bold uppercase">No petty cash data found for selected period</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Balance Report --}}
    <div x-show="tab==='balance'" x-cloak>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">
              <th class="py-3 text-left px-2">Date</th>
              <th class="py-3 text-left px-2">Type</th>
              <th class="py-3 text-left px-2">Description</th>
              <th class="py-3 text-left px-2">Payment Mode</th>
              <th class="py-3 text-right px-2">Amount (₹)</th>
              <th class="py-3 text-right px-2">Balance (₹)</th>
            </tr>
          </thead>
          <tbody>
            @forelse($balanceRows as $row)
            <tr class="border-b border-slate-50 dark:border-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
              <td class="py-3 px-2 font-semibold text-slate-800 dark:text-slate-200">{{ $row['date'] }}</td>
              <td class="py-3 px-2">
                <span class="px-2 py-0.5 rounded-lg text-xs font-bold {{ $row['type']==='Inward' ? 'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400' : 'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400' }}">{{ $row['type'] }}</span>
              </td>
              <td class="py-3 px-2 text-slate-700 dark:text-slate-300">{{ $row['description'] }}</td>
              <td class="py-3 px-2"><span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 rounded-lg text-xs font-bold">{{ $row['payment_mode'] }}</span></td>
              <td class="py-3 px-2 text-right font-bold text-slate-800 dark:text-white">₹{{ number_format($row['amount'],2) }}</td>
              <td class="py-3 px-2 text-right font-black {{ $row['balance']>=0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">₹{{ number_format($row['balance'],2) }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="py-12 text-center text-slate-400 dark:text-slate-500 text-xs font-bold uppercase">No balance data found for selected period</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- ====== PETTY CASH MODAL ====== --}}
  <div x-show="pettyCashOpen" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50" @click.self="pettyCashOpen=false">
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 w-full max-w-md shadow-2xl space-y-4">
      <h3 class="text-lg font-black text-slate-900 dark:text-white">Add Petty Cash</h3>
      <div>
        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Date</label>
        <input type="date" x-model="pcForm.expense_date" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm mt-1 text-slate-900 dark:text-white font-bold focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all" />
      </div>
      <div>
        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Source</label>
        <input type="text" x-model="pcForm.source" placeholder="e.g. Opening, Bank Transfer" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm mt-1 text-slate-900 dark:text-white font-bold focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all" />
      </div>
      <div>
        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Payment Mode</label>
        <select x-model="pcForm.payment_mode" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm mt-1 text-slate-900 dark:text-white font-bold focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
          <option class="dark:bg-slate-800">Cash</option><option class="dark:bg-slate-800">Bank</option><option class="dark:bg-slate-800">UPI</option>
        </select>
      </div>
      <div>
        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Amount</label>
        <input type="number" x-model="pcForm.amount" step="0.01" min="0" placeholder="0.00" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm mt-1 text-slate-900 dark:text-white font-bold focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all" />
      </div>
      <div class="flex gap-3 pt-2">
        <button @click="addPettyCash" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white py-3 rounded-xl font-bold text-sm transition-colors shadow-lg shadow-purple-500/10 dark:shadow-none">Add Petty Cash</button>
        <button @click="pettyCashOpen=false" class="flex-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 py-3 rounded-xl font-bold text-sm transition-colors">Cancel</button>
      </div>
    </div>
  </div>

  {{-- ====== ADD DESCRIPTION MODAL ====== --}}
  <div x-show="descModalOpen" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50" @click.self="descModalOpen=false">
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 w-full max-w-sm shadow-2xl space-y-4">
      <h3 class="text-lg font-black text-slate-900 dark:text-white">Add Description</h3>
      <div>
        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Name</label>
        <input type="text" x-model="newDescName" @keyup.enter="addDesc" placeholder="e.g. TEA, FOOD, OPENING" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm mt-1 text-slate-900 dark:text-white font-bold focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" />
      </div>
      <div class="flex gap-3 pt-2">
        <button @click="addDesc" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold text-sm transition-colors shadow-lg shadow-blue-500/10 dark:shadow-none">Add</button>
        <button @click="descModalOpen=false" class="flex-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 py-3 rounded-xl font-bold text-sm transition-colors">Cancel</button>
      </div>
    </div>
  </div>
</div>

<script>
const _CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function deleteEntry(id) {
  if (!confirm('Delete this entry?')) return;
  fetch('/daily-expense/' + id, {
    method: 'DELETE',
    headers: { 'X-CSRF-TOKEN': _CSRF, 'Accept': 'application/json' }
  }).then(r => r.json()).then(d => { if (d.success) location.reload(); else alert('Error deleting entry'); });
}

function dailyExpense() {
  return {
    tab: 'expense',
    pettyCashOpen: false,
    descModalOpen: false,
    editDescId: null,
    editDescName: '',
    newDescName: '',
    descs: @json($descriptions),
    form: { expense_date: '{{ today()->toDateString() }}', category: '', description: '', payment_mode: 'Cash', amount: '' },
    pcForm: { expense_date: '{{ today()->toDateString() }}', source: '', payment_mode: 'Cash', amount: '' },

    addExpense() {
      if (!this.form.amount || this.form.amount <= 0) { alert('Please enter a valid amount.'); return; }
      fetch('/daily-expense/store', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': _CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(this.form)
      }).then(r => r.json()).then(d => { if (d.success) location.reload(); else alert('Error saving expense'); });
    },

    addPettyCash() {
      if (!this.pcForm.amount || this.pcForm.amount <= 0) { alert('Please enter a valid amount.'); return; }
      fetch('/daily-expense/petty-cash', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': _CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(this.pcForm)
      }).then(r => r.json()).then(d => { if (d.success) { this.pettyCashOpen = false; location.reload(); } else alert('Error saving petty cash'); });
    },

    addDesc() {
      if (!this.newDescName.trim()) return;
      fetch('/daily-expense/descriptions', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': _CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ name: this.newDescName.trim() })
      }).then(r => r.json()).then(d => { if (d.success) { this.descs.push(d.data); this.newDescName = ''; this.descModalOpen = false; } });
    },

    startEditDesc(d) { this.editDescId = d.id; this.editDescName = d.name; },

    saveDesc() {
      fetch('/daily-expense/descriptions/' + this.editDescId, {
        method: 'PUT',
        headers: { 'X-CSRF-TOKEN': _CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ name: this.editDescName })
      }).then(r => r.json()).then(d => {
        if (d.success) {
          const idx = this.descs.findIndex(x => x.id === this.editDescId);
          if (idx !== -1) this.descs[idx].name = d.data.name;
          this.editDescId = null;
        }
      });
    },

    deleteDesc(id) {
      if (!confirm('Delete this description?')) return;
      fetch('/daily-expense/descriptions/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': _CSRF, 'Accept': 'application/json' }
      }).then(r => r.json()).then(d => { if (d.success) this.descs = this.descs.filter(x => x.id !== id); });
    }
  };
}
</script>
@endsection
