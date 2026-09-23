@extends('layouts.tenant')

@section('title', 'Tally Integration')

@section('content')
    <div class="p-4 md:p-6 max-w-7xl mx-auto space-y-6 md:space-y-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight">Tally ERP Integration</h1>
                <p class="text-sm text-slate-500 mt-1">Export transactions and import masters for seamless accounting.</p>
            </div>
            <div class="flex items-center gap-3">
                <div
                    class="px-3 py-1.5 md:px-4 md:py-2 bg-emerald-50 text-emerald-700 rounded-full text-xs md:text-sm font-medium border border-emerald-100 flex items-center gap-2">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                    System Connected
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:gap-8">

            <!-- Export Section -->
            <div class="space-y-6">
                <div class="glass-card rounded-2xl md:rounded-3xl overflow-hidden">
                    <div
                        class="p-5 md:p-6 border-b border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/50">
                        <h2
                            class="text-lg md:text-xl font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            Export Data to Tally
                        </h2>
                    </div>
                    <div class="p-6 md:p-8">
                        <form action="{{ route('tenant.tally.export') }}" method="POST" class="space-y-6">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-slate-700">Data Type</label>
                                    <select name="type"
                                        class="w-full bg-slate-50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 transition-all outline-none text-sm text-slate-800 dark:text-slate-100">
                                        <option value="purchase">Purchase Order</option>
                                        <option value="sales">Sales Order</option>
                                        <option value="products">Product</option>
                                        <option value="customers">Customers</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-slate-700">Export Format</label>
                                    <select name="format"
                                        class="w-full bg-slate-50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 transition-all outline-none text-sm text-slate-800 dark:text-slate-100">
                                        <option value="xml">Tally XML</option>
                                        <option value="excel">Excel (.xlsx)</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-slate-700">Date Range</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="date" name="start_date"
                                            value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                                            class="w-full bg-slate-50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800 text-slate-800 dark:text-slate-100 rounded-xl px-3 py-3 focus:ring-2 focus:ring-indigo-500 transition-all outline-none text-xs md:text-sm">
                                        <input type="date" name="end_date" value="{{ now()->format('Y-m-d') }}"
                                            class="w-full bg-slate-50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800 text-slate-800 dark:text-slate-100 rounded-xl px-3 py-3 focus:ring-2 focus:ring-indigo-500 transition-all outline-none text-xs md:text-sm">
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-slate-50">
                                <button type="submit"
                                    class="w-full md:w-auto px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-2xl shadow-lg shadow-indigo-100 transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    Download Export
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="glass-card rounded-2xl md:rounded-3xl overflow-hidden">
                    <div
                        class="p-5 md:p-6 border-b border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/50">
                        <h2
                            class="text-lg md:text-xl font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Import Masters from Tally
                        </h2>
                    </div>
                    <div class="p-6 md:p-8" x-data="{ isDragging: false, fileName: '' }">
                        <form action="{{ route('tenant.tally.import') }}" method="POST" enctype="multipart/form-data"
                            class="space-y-6">
                            @csrf
                            <div class="relative group">
                                <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-rose-500 rounded-2xl blur opacity-10 group-hover:opacity-20 transition duration-1000 group-hover:duration-200"
                                    :class="{ 'opacity-40 blur-md': isDragging }"></div>
                                <div class="relative flex flex-col items-center justify-center border-2 border-dashed rounded-2xl p-6 md:p-10 bg-slate-50/50 dark:bg-slate-900/30 transition-all cursor-pointer text-center"
                                    :class="{ 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-900/20 scale-[1.01]': isDragging, 'border-slate-200 dark:border-slate-700 group-hover:border-indigo-400': !isDragging }"
                                    @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
                                    @drop.prevent="isDragging = false; $refs.fileInput.files = $event.dataTransfer.files; fileName = $event.dataTransfer.files[0].name">
                                    <input type="file" name="xml_file" x-ref="fileInput"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".xml,.xlsx,.xls,.csv"
                                        @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''">

                                    <div x-show="!fileName" class="flex flex-col items-center transition-all">
                                        <svg class="w-10 h-10 md:w-12 md:h-12 text-slate-300 group-hover:text-indigo-500 transition-colors mb-4"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                            </path>
                                        </svg>
                                        <span class="text-base md:text-lg font-medium text-slate-700">Drop Tally XML or Excel here</span>
                                        <span class="text-xs md:text-sm text-slate-500 mt-1">.xml, .xlsx, .csv files exported from Tally</span>
                                    </div>

                                    <div x-show="fileName" x-cloak
                                        class="flex flex-col items-center animate-in fade-in zoom-in duration-300">
                                        <div
                                            class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center mb-3">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                        <span class="text-base font-bold text-slate-800" x-text="fileName"></span>
                                        <button type="button" @click.stop="fileName = ''; $refs.fileInput.value = ''"
                                            class="mt-2 text-xs text-rose-500 hover:text-rose-700 underline font-medium relative z-10">Remove
                                            file</button>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit"
                                    class="w-full md:w-auto px-6 py-3 bg-slate-900 hover:bg-black text-white font-medium rounded-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="!fileName">
                                    Start Import
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection