@extends('layouts.tenant')

@section('title', 'Tally Import Preview')

@section('content')
<div class="p-4 md:p-6 max-w-7xl mx-auto space-y-6 md:space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight">Tally Import Preview</h1>
            <p class="text-sm text-slate-500 mt-1">Review the data extracted from your Tally file before finalizing the import.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('tenant.tally.index') }}" class="px-4 py-2 bg-white text-slate-700 border border-slate-200 rounded-xl text-sm font-medium hover:bg-slate-50 transition-all">Cancel</a>
            <form action="{{ route('tenant.tally.import.process') }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="import_key" value="{{ $importKey }}">
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-md transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Confirm & Import
                </button>
            </form>
        </div>
    </div>

    @php
        $products = count($parsedData['stockItems'] ?? []);
        $ledgers = count($parsedData['ledgers'] ?? []);
        
        $excelSales = count($parsedData['excelSales'] ?? []);
        $excelPurchases = count($parsedData['excelPurchases'] ?? []);
        $excelReceipts = count($parsedData['excelReceipts'] ?? []);
        $excelPayments = count($parsedData['excelPayments'] ?? []);
        
        $xmlVouchers = $parsedData['vouchers'] ?? [];
        $xmlSales = 0;
        $xmlPurchases = 0;
        $xmlJournals = 0;
        
        foreach($xmlVouchers as $v) {
            $type = strtolower($v['type'] ?? '');
            if(str_contains($type, 'sales') || str_contains($type, 'receipt')) $xmlSales++;
            elseif(str_contains($type, 'purchase') || str_contains($type, 'payment')) $xmlPurchases++;
            else $xmlJournals++;
        }
        
        $totalSales = $excelSales + $xmlSales;
        $totalPurchases = $excelPurchases + $xmlPurchases;
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Sales Card -->
        <div class="glass-card rounded-2xl p-5 border-l-4 border-indigo-500 bg-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Sales Vouchers</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalSales }}</p>
                </div>
                <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Purchases Card -->
        <div class="glass-card rounded-2xl p-5 border-l-4 border-emerald-500 bg-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Purchase Vouchers</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalPurchases }}</p>
                </div>
                <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Products Card -->
        <div class="glass-card rounded-2xl p-5 border-l-4 border-amber-500 bg-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Products (Stock)</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ $products }}</p>
                </div>
                <div class="p-3 bg-amber-50 rounded-xl text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
            </div>
        </div>

        <!-- Ledgers Card -->
        <div class="glass-card rounded-2xl p-5 border-l-4 border-rose-500 bg-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Customers/Suppliers</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ $ledgers }}</p>
                </div>
                <div class="p-3 bg-rose-50 rounded-xl text-rose-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Sections -->
    <div class="glass-card bg-white rounded-3xl overflow-hidden shadow-sm border border-slate-100 mt-8">
        <div class="p-5 md:p-6 flex flex-col gap-6" x-data="{ tab: '{{ $totalSales > 0 ? 'sales' : ($products > 0 ? 'products' : 'ledgers') }}', expandedRow: null }">
            <div class="flex space-x-2 w-full overflow-x-auto pb-2 border-b border-slate-100">
                <button @click="tab = 'sales'" :class="{ 'bg-indigo-50 text-indigo-700 border-indigo-200': tab === 'sales', 'text-slate-500 hover:bg-slate-50 border-transparent': tab !== 'sales' }" class="px-5 py-2.5 rounded-xl text-sm font-medium transition-colors whitespace-nowrap border">
                    Sales Details
                </button>
                <button @click="tab = 'purchases'" :class="{ 'bg-emerald-50 text-emerald-700 border-emerald-200': tab === 'purchases', 'text-slate-500 hover:bg-slate-50 border-transparent': tab !== 'purchases' }" class="px-5 py-2.5 rounded-xl text-sm font-medium transition-colors whitespace-nowrap border">
                    Purchase Details
                </button>
                <button @click="tab = 'products'" :class="{ 'bg-amber-50 text-amber-700 border-amber-200': tab === 'products', 'text-slate-500 hover:bg-slate-50 border-transparent': tab !== 'products' }" class="px-5 py-2.5 rounded-xl text-sm font-medium transition-colors whitespace-nowrap border">
                    Products
                </button>
                <button @click="tab = 'ledgers'" :class="{ 'bg-rose-50 text-rose-700 border-rose-200': tab === 'ledgers', 'text-slate-500 hover:bg-slate-50 border-transparent': tab !== 'ledgers' }" class="px-5 py-2.5 rounded-xl text-sm font-medium transition-colors whitespace-nowrap border">
                    Customers & Suppliers
                </button>
            </div>
            
            <div class="w-full">
                <!-- Sales Tab -->
                <div x-show="tab === 'sales'" class="space-y-4">
                    @if($totalSales > 0)
                        <div class="overflow-x-auto rounded-xl border border-slate-100">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        <th class="p-4">Date</th>
                                        <th class="p-4">Invoice No</th>
                                        <th class="p-4">Party Name</th>
                                        <th class="p-4 text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm">
                                    @foreach($parsedData['excelSales'] ?? [] as $sale)
                                        <tr class="hover:bg-slate-50/50">
                                            <td class="p-4">{{ $sale['date'] ?? '-' }}</td>
                                            <td class="p-4 font-medium">{{ $sale['vch_no'] ?? '-' }}</td>
                                            <td class="p-4">{{ $sale['party'] ?? '-' }}</td>
                                            <td class="p-4 text-right font-medium text-slate-900">₹{{ number_format($sale['amount'] ?? 0, 2) }}</td>
                                            <td class="p-4 text-center">
                                                <button @click="expandedRow = expandedRow === 'excel_sales_{{ $loop->index }}' ? null : 'excel_sales_{{ $loop->index }}'" class="p-1.5 text-slate-400 hover:text-indigo-500 rounded-lg hover:bg-indigo-50 transition" title="View Products">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr x-show="expandedRow === 'excel_sales_{{ $loop->index }}'" style="display: none;" class="bg-slate-50/50">
                                            <td colspan="5" class="p-4">
                                                <div class="bg-white border border-slate-100 rounded-xl overflow-hidden shadow-sm">
                                                    <table class="w-full text-xs text-left">
                                                        <thead class="bg-slate-50 text-slate-500">
                                                            <tr>
                                                                <th class="py-2 px-4">Product Name</th>
                                                                <th class="py-2 px-4 text-center">Qty</th>
                                                                <th class="py-2 px-4 text-right">Price</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-slate-100">
                                                            @forelse($sale['items'] ?? [] as $item)
                                                            <tr>
                                                                <td class="py-2 px-4 font-medium">{{ $item['name'] ?? '-' }}</td>
                                                                <td class="py-2 px-4 text-center">{{ $item['qty'] ?? 1 }}</td>
                                                                <td class="py-2 px-4 text-right">₹{{ number_format($item['price'] ?? 0, 2) }}</td>
                                                            </tr>
                                                            @empty
                                                            <tr>
                                                                <td colspan="3" class="py-3 px-4 text-center text-slate-400">No detailed items found (General Goods/Services assumed).</td>
                                                            </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @foreach($xmlVouchers as $v)
                                        @if(str_contains(strtolower($v['type'] ?? ''), 'sales') || str_contains(strtolower($v['type'] ?? ''), 'receipt'))
                                            <tr class="hover:bg-slate-50/50">
                                                <td class="p-4">{{ $v['date'] ?? '-' }}</td>
                                                <td class="p-4 font-medium">{{ $v['invoice_no'] ?? '-' }}</td>
                                                <td class="p-4">{{ $v['party'] ?? '-' }}</td>
                                                <td class="p-4 text-right font-medium text-slate-900">
                                                    @php
                                                        $grossAmount = 0.0;
                                                        foreach ($v['ledger_entries'] ?? [] as $entry) {
                                                            if (($entry['ledger'] ?? '') === ($v['party'] ?? '')) {
                                                                $grossAmount = abs($entry['amount'] ?? 0);
                                                            }
                                                        }
                                                        if ($grossAmount == 0 && !empty($v['items'])) {
                                                            $grossAmount = array_sum(array_column($v['items'], 'amount'));
                                                        }
                                                    @endphp
                                                    ₹{{ number_format($grossAmount, 2) }}
                                                </td>
                                                <td class="p-4 text-center">
                                                    <button @click="expandedRow = expandedRow === 'xml_sales_{{ $loop->index }}' ? null : 'xml_sales_{{ $loop->index }}'" class="p-1.5 text-slate-400 hover:text-indigo-500 rounded-lg hover:bg-indigo-50 transition" title="View Products">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr x-show="expandedRow === 'xml_sales_{{ $loop->index }}'" style="display: none;" class="bg-slate-50/50">
                                                <td colspan="5" class="p-4">
                                                    <div class="bg-white border border-slate-100 rounded-xl overflow-hidden shadow-sm">
                                                        <table class="w-full text-xs text-left">
                                                            <thead class="bg-slate-50 text-slate-500">
                                                                <tr>
                                                                    <th class="py-2 px-4">Product Name</th>
                                                                    <th class="py-2 px-4 text-center">Qty</th>
                                                                    <th class="py-2 px-4 text-right">Amount</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-slate-100">
                                                                @forelse($v['items'] ?? [] as $item)
                                                                <tr>
                                                                    <td class="py-2 px-4 font-medium">{{ $item['name'] ?? '-' }}</td>
                                                                    <td class="py-2 px-4 text-center">{{ $item['qty'] ?? 1 }}</td>
                                                                    <td class="py-2 px-4 text-right">₹{{ number_format($item['amount'] ?? ($item['price'] ?? 0), 2) }}</td>
                                                                </tr>
                                                                @empty
                                                                <tr>
                                                                    <td colspan="3" class="py-3 px-4 text-center text-slate-400">No detailed items found.</td>
                                                                </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-10 text-slate-500">No sales records found in this file.</div>
                    @endif
                </div>

                <!-- Purchases Tab -->
                <div x-show="tab === 'purchases'" style="display: none;" class="space-y-4">
                    @if($totalPurchases > 0)
                        <div class="overflow-x-auto rounded-xl border border-slate-100">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        <th class="p-4">Date</th>
                                        <th class="p-4">Invoice No</th>
                                        <th class="p-4">Party Name</th>
                                        <th class="p-4 text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm">
                                    @foreach($parsedData['excelPurchases'] ?? [] as $purchase)
                                        <tr class="hover:bg-slate-50/50">
                                            <td class="p-4">{{ $purchase['date'] ?? '-' }}</td>
                                            <td class="p-4 font-medium">{{ $purchase['vch_no'] ?? '-' }}</td>
                                            <td class="p-4">{{ $purchase['party'] ?? '-' }}</td>
                                            <td class="p-4 text-right font-medium text-slate-900">₹{{ number_format($purchase['amount'] ?? 0, 2) }}</td>
                                            <td class="p-4 text-center">
                                                <button @click="expandedRow = expandedRow === 'excel_purchases_{{ $loop->index }}' ? null : 'excel_purchases_{{ $loop->index }}'" class="p-1.5 text-slate-400 hover:text-emerald-500 rounded-lg hover:bg-emerald-50 transition" title="View Products">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr x-show="expandedRow === 'excel_purchases_{{ $loop->index }}'" style="display: none;" class="bg-slate-50/50">
                                            <td colspan="5" class="p-4">
                                                <div class="bg-white border border-slate-100 rounded-xl overflow-hidden shadow-sm">
                                                    <table class="w-full text-xs text-left">
                                                        <thead class="bg-slate-50 text-slate-500">
                                                            <tr>
                                                                <th class="py-2 px-4">Product Name</th>
                                                                <th class="py-2 px-4 text-center">Qty</th>
                                                                <th class="py-2 px-4 text-right">Price</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-slate-100">
                                                            @forelse($purchase['items'] ?? [] as $item)
                                                            <tr>
                                                                <td class="py-2 px-4 font-medium">{{ $item['name'] ?? '-' }}</td>
                                                                <td class="py-2 px-4 text-center">{{ $item['qty'] ?? 1 }}</td>
                                                                <td class="py-2 px-4 text-right">₹{{ number_format($item['price'] ?? 0, 2) }}</td>
                                                            </tr>
                                                            @empty
                                                            <tr>
                                                                <td colspan="3" class="py-3 px-4 text-center text-slate-400">No detailed items found.</td>
                                                            </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @foreach($xmlVouchers as $v)
                                        @if(str_contains(strtolower($v['type'] ?? ''), 'purchase') || str_contains(strtolower($v['type'] ?? ''), 'payment'))
                                            <tr class="hover:bg-slate-50/50">
                                                <td class="p-4">{{ $v['date'] ?? '-' }}</td>
                                                <td class="p-4 font-medium">{{ $v['invoice_no'] ?? '-' }}</td>
                                                <td class="p-4">{{ $v['party'] ?? '-' }}</td>
                                                <td class="p-4 text-right font-medium text-slate-900">
                                                    @php
                                                        $grossAmount = 0.0;
                                                        foreach ($v['ledger_entries'] ?? [] as $entry) {
                                                            if (($entry['ledger'] ?? '') === ($v['party'] ?? '')) {
                                                                $grossAmount = abs($entry['amount'] ?? 0);
                                                            }
                                                        }
                                                        if ($grossAmount == 0 && !empty($v['items'])) {
                                                            $grossAmount = array_sum(array_column($v['items'], 'amount'));
                                                        }
                                                    @endphp
                                                    ₹{{ number_format($grossAmount, 2) }}
                                                </td>
                                                <td class="p-4 text-center">
                                                    <button @click="expandedRow = expandedRow === 'xml_purchases_{{ $loop->index }}' ? null : 'xml_purchases_{{ $loop->index }}'" class="p-1.5 text-slate-400 hover:text-emerald-500 rounded-lg hover:bg-emerald-50 transition" title="View Products">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr x-show="expandedRow === 'xml_purchases_{{ $loop->index }}'" style="display: none;" class="bg-slate-50/50">
                                                <td colspan="5" class="p-4">
                                                    <div class="bg-white border border-slate-100 rounded-xl overflow-hidden shadow-sm">
                                                        <table class="w-full text-xs text-left">
                                                            <thead class="bg-slate-50 text-slate-500">
                                                                <tr>
                                                                    <th class="py-2 px-4">Product Name</th>
                                                                    <th class="py-2 px-4 text-center">Qty</th>
                                                                    <th class="py-2 px-4 text-right">Amount</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-slate-100">
                                                                @forelse($v['items'] ?? [] as $item)
                                                                <tr>
                                                                    <td class="py-2 px-4 font-medium">{{ $item['name'] ?? '-' }}</td>
                                                                    <td class="py-2 px-4 text-center">{{ $item['qty'] ?? 1 }}</td>
                                                                    <td class="py-2 px-4 text-right">₹{{ number_format($item['amount'] ?? ($item['price'] ?? 0), 2) }}</td>
                                                                </tr>
                                                                @empty
                                                                <tr>
                                                                    <td colspan="3" class="py-3 px-4 text-center text-slate-400">No detailed items found.</td>
                                                                </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-10 text-slate-500">No purchase records found in this file.</div>
                    @endif
                </div>

                <!-- Products Tab -->
                <div x-show="tab === 'products'" style="display: none;" class="space-y-4">
                    @if($products > 0)
                        <div class="overflow-x-auto rounded-xl border border-slate-100">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        <th class="p-4">Product Name</th>
                                        <th class="p-4">HSN</th>
                                        <th class="p-4">Unit</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm">
                                    @foreach($parsedData['stockItems'] ?? [] as $item)
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="p-4 font-medium text-slate-900">{{ $item['name'] ?? '-' }}</td>
                                        <td class="p-4">{{ $item['hsn'] ?? '-' }}</td>
                                        <td class="p-4">{{ $item['unit'] ?? 'Nos' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-10 text-slate-500">No products found in this file.</div>
                    @endif
                </div>

                <!-- Ledgers Tab -->
                <div x-show="tab === 'ledgers'" style="display: none;" class="space-y-4">
                    @if($ledgers > 0)
                        <div class="overflow-x-auto rounded-xl border border-slate-100">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        <th class="p-4">Ledger Name</th>
                                        <th class="p-4">Group (Parent)</th>
                                        <th class="p-4">GSTIN</th>
                                        <th class="p-4">Phone</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm">
                                    @foreach($parsedData['ledgers'] ?? [] as $ledger)
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="p-4 font-medium text-slate-900">{{ $ledger['name'] ?? '-' }}</td>
                                        <td class="p-4">
                                            @if(str_contains(strtolower($ledger['parent'] ?? ''), 'debtors'))
                                                <span class="inline-flex items-center px-2 py-1 rounded-md bg-blue-50 text-blue-700 text-xs font-medium ring-1 ring-inset ring-blue-700/10">Sundry Debtors</span>
                                            @elseif(str_contains(strtolower($ledger['parent'] ?? ''), 'creditors'))
                                                <span class="inline-flex items-center px-2 py-1 rounded-md bg-orange-50 text-orange-700 text-xs font-medium ring-1 ring-inset ring-orange-700/10">Sundry Creditors</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-md bg-slate-50 text-slate-700 text-xs font-medium ring-1 ring-inset ring-slate-700/10">{{ $ledger['parent'] ?? '-' }}</span>
                                            @endif
                                        </td>
                                        <td class="p-4">{{ $ledger['gstin'] ?? '-' }}</td>
                                        <td class="p-4">{{ $ledger['phone'] ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-10 text-slate-500">No customer or supplier ledgers found.</div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
