<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Purchase;
use App\Models\DailyExpense;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Setting;
use App\Models\Category;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\StockLog;
use App\Services\TallyService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TallyController extends Controller
{
    protected $tallyService;

    public function __construct(TallyService $tallyService)
    {
        $this->tallyService = $tallyService;
    }

    public function index()
    {
        $settings = [
            'tally_sales_ledger' => Setting::get('tally_sales_ledger', 'Sales Account'),
            'tally_purchase_ledger' => Setting::get('tally_purchase_ledger', 'Purchase Account'),
            'tally_cgst_ledger' => Setting::get('tally_cgst_ledger', 'Output CGST'),
            'tally_sgst_ledger' => Setting::get('tally_sgst_ledger', 'Output SGST'),
            'tally_input_cgst_ledger' => Setting::get('tally_input_cgst_ledger', 'Input CGST'),
            'tally_input_sgst_ledger' => Setting::get('tally_input_sgst_ledger', 'Input SGST'),
            'tally_roundoff_ledger' => Setting::get('tally_roundoff_ledger', 'Round Off'),
        ];

        return view('tenant.tally.index', compact('settings'));
    }

    public function export(Request $request)
    {
        $type = $request->input('type', 'sales');
        $format = $request->input('format', 'xml');
        $from = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('end_date', now()->format('Y-m-d'));

        $data = null;
        $xml = '';
        $filename = "Tally_{$type}_{$from}_to_{$to}";

        switch ($type) {
            case 'sales':
                $data = Bill::with('items')->whereBetween('bill_date', [$from, $to])->get();
                if ($format === 'xml') $xml = $this->tallyService->generateSalesXml($data);
                break;

            case 'purchase':
                $data = Purchase::with(['vendor', 'items'])->whereBetween('invoice_date', [$from, $to])->get();
                if ($format === 'xml') $xml = $this->tallyService->generatePurchaseXml($data);
                break;

            case 'customers':
                $data = Customer::all();
                if ($format === 'xml') $xml = $this->tallyService->generateLedgerXml($data, collect([])); 
                break;
                
            case 'products':
                $data = Category::all();
                if ($format === 'xml') $xml = $this->tallyService->generateProductsXml($data);
                break;
        }

        if ($format === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\TallyExport($data, $type), $filename . '.xlsx');
        }

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => "attachment; filename=\"{$filename}.xml\"",
        ]);
    }

    public function import(Request $request)
    {
        // Increase limits for large XML files
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');

        $file = $request->file('xml_file');

        if (!$file || $file->getError() !== UPLOAD_ERR_OK) {
            $errorCode = $file ? $file->getError() : 'Unknown';
            return back()->with('error', "Upload failed. PHP Error Code: {$errorCode}. This usually means the file is larger than the 'upload_max_filesize' allowed by your server.");
        }

        $extension = $file->getClientOriginalExtension();
        $realPath = $file->getRealPath();
        $size = $file->getSize();

        $ledgers = [];
        $stockItems = [];
        $excelPurchases = [];
        $excelPayments = [];
        $excelSales = [];
        $excelReceipts = [];

        if (in_array($extension, ['xlsx', 'xls', 'csv'])) {
            try {
                $import = new class implements \Maatwebsite\Excel\Concerns\ToArray {
                    public function array(array $array) {}
                };
                $sheets = \Maatwebsite\Excel\Facades\Excel::toArray($import, $realPath);
                
                $totalRowsFound = 0;
                if (!empty($sheets)) {
                    foreach ($sheets as $sheet) {
                        if (!empty($sheet)) {
                            $totalRowsFound += count($sheet);
                            $sheetLedgers = $this->tallyService->parseStandardExcelLedgers($sheet);
                            $sheetStock = $this->tallyService->parseStandardExcelStockItems($sheet);
                            
                            $ledgers = array_merge($ledgers, $sheetLedgers);
                            $stockItems = array_merge($stockItems, $sheetStock);
                            
                            $parsedPurchases = $this->tallyService->parseStandardExcelPurchases($sheet);
                            $excelPurchases = array_merge($excelPurchases, $parsedPurchases['purchases'] ?? $parsedPurchases);
                            if (isset($parsedPurchases['payments'])) {
                                $excelPayments = array_merge($excelPayments, $parsedPurchases['payments']);
                            }
                            
                            $parsedSales = $this->tallyService->parseStandardExcelSales($sheet);
                            $excelSales = array_merge($excelSales, $parsedSales['sales'] ?? $parsedSales);
                            if (isset($parsedSales['receipts'])) {
                                $excelReceipts = array_merge($excelReceipts, $parsedSales['receipts']);
                            }
                        }
                    }
                }
                \Illuminate\Support\Facades\Log::info("Excel Sheets: " . count($sheets) . ", Total Rows: {$totalRowsFound}, Parsed Products: " . count($stockItems));
            } catch (\Exception $e) {
                return back()->with('error', "Excel Parsing Failed: " . $e->getMessage());
            }
        } else {
            // Use stream reading for XML files
            $handle = fopen($realPath, 'r');
            $content = $size > 0 ? fread($handle, $size) : '';
            fclose($handle);

            try {
                $ledgers = $this->tallyService->parseLedgersXml($content);
                $stockItems = $this->tallyService->parseStockItemsXml($content);
            } catch (\Exception $e) {
                $snippet = htmlspecialchars(substr($content, 0, 200));
                return back()->with('error', "XML Parsing Failed: " . $e->getMessage() . " | File Start: [{$snippet}...]");
            }
        }

        // ── Import Transactions (Vouchers) ───────────────────────────────────
        $vouchers = [];
        try {
            if (isset($content) && $content) {
                $vouchers = $this->tallyService->parseVouchersXml($content);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Tally Voucher parsing failed: " . $e->getMessage());
        }
        
        $parsedData = [
            'ledgers' => $ledgers ?? [],
            'stockItems' => $stockItems ?? [],
            'excelPurchases' => $excelPurchases ?? [],
            'excelPayments' => $excelPayments ?? [],
            'excelSales' => $excelSales ?? [],
            'excelReceipts' => $excelReceipts ?? [],
            'vouchers' => $vouchers ?? [],
        ];
        
        $importKey = 'tally_import_' . uniqid();
        \Illuminate\Support\Facades\Storage::disk('local')->put('temp/' . $importKey . '.json', json_encode($parsedData));
        
        return view('tenant.tally.preview', compact('parsedData', 'importKey'));
    }

    public function processImport(Request $request)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');

        $importKey = $request->input('import_key');
        $json = \Illuminate\Support\Facades\Storage::disk('local')->get('temp/' . $importKey . '.json');
        $parsedData = $json ? json_decode($json, true) : null;
        
        if (!$parsedData) {
            return redirect()->route('tenant.tally.index')->with('error', 'Import session expired. Please upload the file again.');
        }

        $ledgers = $parsedData['ledgers'] ?? [];
        $stockItems = $parsedData['stockItems'] ?? [];
        $excelPurchases = $parsedData['excelPurchases'] ?? [];
        $excelPayments = $parsedData['excelPayments'] ?? [];
        $excelSales = $parsedData['excelSales'] ?? [];
        $excelReceipts = $parsedData['excelReceipts'] ?? [];
        $vouchers = $parsedData['vouchers'] ?? [];
$count = 0;
        $customers = 0;
        $suppliers = 0;
        $productsFound = 0;
        $productsImported = 0;

        // Import Products (Stock Items)
        if (!empty($stockItems)) {
            $productsFound = count($stockItems);
            foreach ($stockItems as $item) {
                $existing = Category::where('product_name', $item['name'])->first();
                if (!$existing) {
                    Category::create([
                        'product_name' => $item['name'],
                        'hsn'          => $item['hsn'],
                        'unit'         => $item['unit'] ?? 'Nos',
                        'is_active'    => true,
                        'stock'        => $item['stock'] ?? 0,
                        'mrp'          => $item['mrp'] ?? 0,
                        'dealer_price' => $item['dealer_price'] ?? 0,
                    ]);
                    $productsImported++;
                } else {
                    // Update existing stock and prices if they are provided
                    if (($item['stock'] ?? 0) > 0 || ($item['mrp'] ?? 0) > 0 || ($item['dealer_price'] ?? 0) > 0) {
                        $updateData = [];
                        if (($item['stock'] ?? 0) != 0) $updateData['stock'] = $item['stock'];
                        if (($item['mrp'] ?? 0) > 0) $updateData['mrp'] = $item['mrp'];
                        if (($item['dealer_price'] ?? 0) > 0) $updateData['dealer_price'] = $item['dealer_price'];
                        
                        if (!empty($updateData)) {
                            $existing->update($updateData);
                        }
                    }
                }
            }
        }

        foreach ($ledgers as $l) {
            $parent = strtolower($l['parent']);

            // Sanitize phone: keep digits only, max 15 chars
            $rawPhone = preg_replace('/\D/', '', $l['phone'] ?? '');
            $phone = strlen($rawPhone) >= 7 ? substr($rawPhone, 0, 15) : null;

            if (str_contains($parent, 'debtors')) {
                $existing = Customer::where('name', $l['name'])->first();

                $data = ['gstin' => $l['gstin'] ?: ($existing->gstin ?? null)];
                if (!empty($l['email']))
                    $data['email'] = $l['email'];
                if (!empty($l['address']))
                    $data['address'] = $l['address'];
                if (!empty($l['city']))
                    $data['city'] = $l['city'];
                if (!empty($l['state']))
                    $data['state'] = $l['state'];
                if (!empty($l['pincode']))
                    $data['pincode'] = $l['pincode'];
                if ($phone && (!$existing || !$existing->phone))
                    $data['phone'] = $phone;

                if ($existing) {
                    $existing->update($data);
                } else {
                    Customer::create(array_merge(['name' => $l['name']], $data));
                }
                $customers++;
                $count++;

            } elseif (str_contains($parent, 'creditors')) {
                $existing = Supplier::where('name', $l['name'])->first();

                $data = ['gstin' => $l['gstin'] ?: ($existing->gstin ?? null)];
                if (!empty($l['email']))
                    $data['email'] = $l['email'];
                if (!empty($l['address']))
                    $data['address'] = $l['address'];
                if (!empty($l['city']))
                    $data['city'] = $l['city'];
                if (!empty($l['state']))
                    $data['state'] = $l['state'];
                if (!empty($l['pincode']))
                    $data['pincode'] = $l['pincode'];
                if ($phone && (!$existing || !$existing->phone))
                    $data['phone'] = $phone;

                if ($existing) {
                    $existing->update($data);
                } else {
                    Supplier::create(array_merge(['name' => $l['name']], $data));
                }
                $suppliers++;
                $count++;
            }
        }



        $importedBills = 0;
        $importedPurchases = 0;

        // Import Excel Sales
        if (!empty($excelSales)) {
            foreach ($excelSales as $es) {
                $customer = Customer::where('name', $es['party'])->first();
                if (!$customer) {
                    $customer = Customer::create([
                        'name' => $es['party']
                    ]);
                    $customers++;
                }

                $bill = Bill::firstOrNew(['invoice_no' => $es['vch_no']]);
                
                $bill->customer_id = $customer->id;
                $bill->customer_name = $customer->name;
                $bill->bill_date = $es['date'];
                $bill->grand_total = $es['amount'];
                $bill->bill_type = 'billing';
                
                if (!$bill->exists) {
                    $bill->subtotal = $es['amount'];
                    $bill->discount_percent = 0;
                    $bill->discount_amount = 0;
                    $bill->gst_percent = 0;
                    $bill->gst_amount = 0;
                    $bill->status = 'completed';
                    $bill->remarks = 'Imported from Tally Excel Daybook';
                    $bill->paid_amount = $es['amount'];
                    $bill->payment_mode = 'cash';
                }
                
                $bill->save();

                // Add Items
                if (!empty($es['items'])) {
                    $bill->items()->delete();
                    foreach ($es['items'] as $item) {
                        $bill->items()->create([
                            'product_name' => $item['name'],
                            'quantity' => $item['qty'],
                            'mrp' => $item['price'],
                        ]);
                    }
                } elseif ($bill->items()->count() == 0) {
                    $bill->items()->create([
                        'product_name' => 'General Goods / Services',
                        'quantity' => 1,
                        'mrp' => $es['amount'],
                    ]);
                }
                
                $importedBills++;
            }
        }

        // Import Excel Receipts
        if (!empty($excelReceipts)) {
            foreach ($excelReceipts as $rec) {
                $customer = Customer::where('name', $rec['party'])->first();
                if (!$customer) continue;

                $existingReceipt = \App\Models\Instalment::where('payment_reference', $rec['vch_no'])
                    ->where('customer_id', $customer->id)
                    ->first();

                if (!$existingReceipt) {
                    \App\Models\Instalment::create([
                        'customer_id' => $customer->id,
                        'paid_date' => $rec['date'],
                        'amount_paid' => $rec['amount'],
                        'payment_mode' => 'cash/bank',
                        'payment_reference' => $rec['vch_no'],
                        'remarks' => 'Imported Receipt from Tally Excel'
                    ]);
                }
            }
        }

        // Import Excel Purchases
        if (!empty($excelPurchases)) {
            foreach ($excelPurchases as $ep) {
                $vendor = Supplier::where('name', $ep['party'])->first();
                if (!$vendor) {
                    $vendor = Supplier::create([
                        'name' => $ep['party'],
                        'status' => 'active'
                    ]);
                    $suppliers++;
                }

                $purchase = Purchase::firstOrNew(['invoice_ref' => $ep['vch_no']]);
                
                $purchase->vendor_id = $vendor->id;
                $purchase->invoice_date = $ep['date'];
                $purchase->total_amount = $ep['amount'];
                
                if (!$purchase->exists) {
                    $purchase->discount_percent = 0;
                    $purchase->discount_amount = 0;
                    $purchase->gst_percent = 0;
                    $purchase->gst_amount = 0;
                    $purchase->status = 'Pending';
                    $purchase->remark = 'Imported from Tally Excel Daybook';
                    $purchase->balance_amount = $ep['amount'];
                } else {
                    $paidAmount = $purchase->settlements()->sum('amount');
                    $purchase->balance_amount = max(0, $ep['amount'] - $paidAmount);
                    $purchase->status = $purchase->balance_amount <= 0 ? 'Completed' : 'Pending';
                }
                
                $purchase->save();

                // Add Items
                if (!empty($ep['items'])) {
                    $purchase->items()->delete();
                    foreach ($ep['items'] as $item) {
                        $purchase->items()->create([
                            'product_name' => $item['name'],
                            'quantity' => $item['qty'],
                            'buy_price' => $item['price'],
                            'total_amount' => $item['amount'],
                            'grand_total' => $item['amount'],
                        ]);
                    }
                }
                
                $importedPurchases++;
            }
        }
        // Process Excel Payments
        if (!empty($excelPayments)) {
            foreach ($excelPayments as $pay) {
                $vendor = Supplier::where('name', $pay['party'])->first();
                if (!$vendor) continue;

                $existingSettlement = \App\Models\PurchaseSettlement::where('document_number', $pay['vch_no'])
                    ->where('entry_type', 'payment')
                    ->where('vendor_id', $vendor->id)
                    ->first();

                if (!$existingSettlement) {
                    $remaining_amount = $pay['amount'];
                    
                    // Find pending purchases for this vendor
                    $pending_purchases = Purchase::where('vendor_id', $vendor->id)
                        ->where('status', '!=', 'Completed')
                        ->where('balance_amount', '>', 0)
                        ->orderBy('invoice_date', 'asc')
                        ->get();
                    
                    \App\Models\PurchaseSettlement::create([
                        'purchase_id' => $pending_purchases->first()->id ?? null,
                        'vendor_id' => $vendor->id,
                        'date' => $pay['date'],
                        'amount' => $pay['amount'],
                        'payment_mode' => 'cash/bank',
                        'document_number' => $pay['vch_no'],
                        'description' => 'Imported from Tally Excel Daybook',
                        'entry_type' => 'payment'
                    ]);

                    foreach ($pending_purchases as $p) {
                        if ($remaining_amount <= 0) break;

                        $payment_to_apply = min($remaining_amount, $p->balance_amount);
                        $p->balance_amount -= $payment_to_apply;
                        $remaining_amount -= $payment_to_apply;

                        if ($p->balance_amount <= 0) {
                            $p->balance_amount = 0;
                            $p->status = 'Completed';
                        }
                        $p->save();
                    }

                    if ($remaining_amount > 0) {
                        $vendor->advance_balance += $remaining_amount;
                        $vendor->save();
                    }
                }
            }
        }

        foreach ($vouchers as $v) {
            $type = strtolower($v['type']);
            $partyName = $v['party'];

            // Calculate totals from ledger entries
            $grossAmount = 0.0;
            $subtotal = 0.0;
            $gstAmount = 0.0;
            $roundOff = 0.0;

            foreach ($v['ledger_entries'] as $entry) {
                $ledgerName = $entry['ledger'];
                $amount = $entry['amount'];

                if ($ledgerName === $partyName) {
                    $grossAmount = abs($amount);
                } elseif (str_contains(strtolower($ledgerName), 'sales') || str_contains(strtolower($ledgerName), 'purchase')) {
                    $subtotal += abs($amount);
                } elseif (str_contains(strtolower($ledgerName), 'cgst') || str_contains(strtolower($ledgerName), 'sgst') || str_contains(strtolower($ledgerName), 'igst')) {
                    $gstAmount += abs($amount);
                } elseif (str_contains(strtolower($ledgerName), 'round')) {
                    $roundOff = $amount;
                }
            }

            if ($grossAmount == 0 && !empty($v['items'])) {
                $grossAmount = array_sum(array_column($v['items'], 'amount'));
                $subtotal = $grossAmount;
            }

            if (str_contains($type, 'sales') || str_contains($type, 'receipt')) {
                // Find Customer
                $customer = Customer::where('name', $partyName)->first();

                // Create or update Bill (Sales Invoice)
                $bill = Bill::updateOrCreate(
                    ['invoice_no' => $v['invoice_no']],
                    [
                        'bill_type' => 'billing',
                        'customer_id' => $customer?->id,
                        'customer_phone' => $customer?->phone,
                        'customer_email' => $customer?->email,
                        'customer_name' => $partyName,
                        'customer_address' => $customer?->address,
                        'customer_gstin' => $customer?->gstin,
                        'bill_date' => $v['date'],
                        'subtotal' => $subtotal ?: $grossAmount,
                        'discount_amount' => 0,
                        'discount_percent' => 0,
                        'gst_amount' => $gstAmount,
                        'gst_percent' => $subtotal > 0 ? round(($gstAmount / $subtotal) * 100) : 0,
                        'round_off' => $roundOff,
                        'grand_total' => $grossAmount,
                        'paid_amount' => $grossAmount,
                        'payment_mode' => 'cash',
                        'status' => 'completed',
                        'remarks' => 'Imported from Tally XML',
                    ]
                );

                // Import Items if present
                if (!empty($v['items'])) {
                    $bill->items()->delete();
                    foreach ($v['items'] as $item) {
                        $bill->items()->create([
                            'product_name' => $item['name'],
                            'quantity' => $item['qty'],
                            'mrp' => $item['price'],
                        ]);
                    }
                } else {
                    if ($bill->items()->count() == 0) {
                        $bill->items()->create([
                            'product_name' => 'General Goods / Services',
                            'quantity' => 1,
                            'mrp' => $grossAmount,
                        ]);
                    }
                }
                $importedBills++;

            } elseif (str_contains($type, 'purchase') || str_contains($type, 'payment')) {
                // Find Vendor / Supplier
                $vendor = Supplier::where('name', $partyName)->first();

                // Create or update Purchase
                $purchase = Purchase::firstOrNew(['invoice_ref' => $v['invoice_no']]);
                
                $purchase->vendor_id = $vendor?->id;
                $purchase->invoice_date = $v['date'];
                $purchase->total_amount = $grossAmount;
                
                if (!$purchase->exists) {
                    $purchase->discount_percent = 0;
                    $purchase->discount_amount = 0;
                    $purchase->gst_percent = $subtotal > 0 ? round(($gstAmount / $subtotal) * 100) : 0;
                    $purchase->gst_amount = $gstAmount;
                    $purchase->status = 'Pending';
                    $purchase->remark = 'Imported from Tally XML';
                    $purchase->balance_amount = $grossAmount;
                } else {
                    $paidAmount = $purchase->settlements()->sum('amount');
                    $purchase->balance_amount = max(0, $grossAmount - $paidAmount);
                    $purchase->status = $purchase->balance_amount <= 0 ? 'Completed' : 'Pending';
                }
                
                $purchase->save();

                // Import Items if present
                if (!empty($v['items'])) {
                    $purchase->items()->delete();
                    foreach ($v['items'] as $item) {
                        $purchase->items()->create([
                            'product_name' => $item['name'],
                            'quantity' => $item['qty'],
                            'buy_price' => $item['price'],
                            'total_amount' => $item['amount'],
                            'grand_total' => $item['amount'],
                        ]);
                        // Update stock
                        $product = Category::where('product_name', $item['name'])->first();
                        if ($product) {
                            StockLog::log($product->id, $item['qty'], 'in', $purchase->id, 'purchase', 'Imported Purchase Voucher from Tally');
                            $product->increment('stock', $item['qty']);
                        }
                    }
                }
                $importedPurchases++;

            } elseif (str_contains($type, 'journal') || str_contains($type, 'contra')) {
                // Determine if it's a Stock Journal or Accounting Journal/Contra
                if (!empty($v['items'])) {
                    // Stock Journal -> Inventory mapping
                    foreach ($v['items'] as $item) {
                        $product = Category::where('product_name', $item['name'])->first();
                        if ($product) {
                            $direction = $item['amount'] < 0 ? 'out' : 'in';
                            $qty = abs($item['qty']);
                            StockLog::log($product->id, $qty, $direction, null, 'stock_journal', 'Imported Stock Journal from Tally');
                            
                            if ($direction === 'in') {
                                $product->increment('stock', $qty);
                            } else {
                                $product->decrement('stock', $qty);
                            }
                        }
                    }
                } else {
                    // Accounting Journal / Contra
                    $entry = JournalEntry::create([
                        'date' => $v['date'],
                        'reference_number' => $v['invoice_no'],
                        'description' => "Imported {$type} from Tally",
                        'total_amount' => $grossAmount ?: $subtotal
                    ]);

                    foreach ($v['ledger_entries'] as $entryLine) {
                        $amount = (float) $entryLine['amount'];
                        $entry->lines()->create([
                            'chart_of_account_id' => null, // Would require syncing Tally Ledgers to ChartOfAccounts
                            'description' => $entryLine['ledger'],
                            'debit' => $amount > 0 ? 0 : abs($amount), // Negative amount in Tally usually means Debit depending on context
                            'credit' => $amount > 0 ? $amount : 0
                        ]);
                    }
                }
            }
        }

        if ($count == 0 && $productsFound == 0 && $importedBills == 0 && $importedPurchases == 0) {
            if (in_array($extension, ['xlsx', 'xls', 'csv']) && isset($sheets[0])) {
                $snippet = json_encode(array_slice($sheets[0], 0, 5));
                return back()->with('error', "No valid data mapped from Excel. First rows: {$snippet}");
            }
            $snippet = htmlspecialchars(substr($content, 0, 200));
            return back()->with('error', "No valid ledgers or vouchers found in the file. Start: [{$snippet}...]");
        }
        \Illuminate\Support\Facades\Storage::disk('local')->delete('temp/' . $importKey . '.json');

        $msg = "✅ Imported {$count} ledger records ({$customers} customers, {$suppliers} suppliers).";
        if ($productsFound > 0) {
            $msg .= " Found {$productsFound} products in file, successfully added {$productsImported} new products.";
        }
        if ($importedBills > 0 || $importedPurchases > 0) {
            $msg .= " Successfully synchronized {$importedBills} Sales invoices and {$importedPurchases} Purchase orders.";
        }

        return redirect()->route('tenant.tally.index')->with('success', $msg);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->only([
            'tally_sales_ledger',
            'tally_purchase_ledger',
            'tally_cgst_ledger',
            'tally_sgst_ledger',
            'tally_input_cgst_ledger',
            'tally_input_sgst_ledger',
            'tally_roundoff_ledger'
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('success', 'Tally ledger mappings updated.');
    }
}

