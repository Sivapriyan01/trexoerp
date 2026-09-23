<?php

namespace App\Http\Controllers\Tenant;
 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseController extends Controller
{
    /**
     * 🧾 Show Purchase Form
     */
    public function create()
    {
        $vendors = \App\Models\Supplier::where('is_active', true)->orderBy('name')->get();
        $products = \App\Models\Category::where('is_active', true)->orderBy('product_name')->get();
        
        $stockHealth = [
            'total' => \App\Models\Category::count(),
            'low' => \App\Models\Category::where('stock', '>', 0)->where('stock', '<=', 10)->count(),
            'out' => \App\Models\Category::where('stock', '<=', 0)->count(),
        ];
        $stockHealth['percentage'] = $stockHealth['total'] > 0 ? round((($stockHealth['total'] - $stockHealth['out']) / $stockHealth['total']) * 100) : 100;

        return view('tenant.purchase.create', compact('vendors', 'products', 'stockHealth'));
    }

    /**
     * 💾 Store Purchase + Items
     */
    public function store(Request $request)
    {
        Log::info('Purchase store attempt', $request->all());
        // ✅ Validation
        $request->validate([
            'invoice_ref' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'items' => 'required|array',
            'items.*.product_name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.buy_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            // 🧾 Calculate Totals if missing
            $items = collect($request->items);
            $subtotal = $items->sum(fn($i) => (float)($i['total_amount'] ?? 0));
            $totalAmount = $request->total_amount ?: $subtotal;

            // 🧾 Create Purchase
            $purchase = Purchase::create([
                'vendor_id' => $request->vendor_id ?: null,
                'invoice_ref' => $request->invoice_ref,
                'invoice_date' => $request->invoice_date,
                'remark' => $request->remark,
                'discount_percent' => $request->discount_percent ?? 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'gst_percent' => $request->gst_percent ?? 0,
                'gst_amount' => $request->gst_amount ?? 0,
                'total_amount' => $totalAmount,
                'balance_amount' => $totalAmount,
                'status' => 'Pending',
            ]);

            // 📦 Save Items
            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    if (empty($item['product_name'])) continue;

                    $barcode = $item['barcode'] ?? null;
                    if ($barcode) {
                        $product = \App\Models\Category::where('barcode', $barcode)->first();
                        if ($product) {
                            $product->increment('stock', $item['quantity'] ?? 0);
                            if (isset($item['dealer_price'])) $product->update(['dealer_price' => $item['dealer_price']]);
                            if (isset($item['mrp'])) $product->update(['mrp' => $item['mrp']]);

                            // Trigger Auto-Allocation for Pre-Orders
                            \App\Services\PreOrderService::allocateStockForProduct($product->id);
                        }
                    }

                    $purchase->items()->create([
                        'product_name' => $item['product_name'],
                        'product_type' => $item['product_type'] ?? null,
                        'size' => $item['size'] ?? null,
                        'brand' => $item['brand'] ?? null,
                        'quantity' => $item['quantity'] ?? 0,
                        'buy_price' => $item['buy_price'] ?? 0,
                        'total_amount' => $item['total_amount'] ?? 0,
                        'discount_percent' => $item['discount_percent'] ?? 0,
                        'discount_amount' => $item['discount_amount'] ?? 0,
                        'gst_percent' => $item['gst_percent'] ?? 0,
                        'gst_amount' => $item['gst_amount'] ?? 0,
                        'grand_total' => $item['grand_total'] ?? 0,
                        'profit_percent' => $item['profit_percent'] ?? 0,
                        'profit_amount' => $item['profit_amount'] ?? 0,
                        'mrp' => $item['mrp'] ?? null,
                        'dealer_price' => $item['dealer_price'] ?? null,
                        'barcode' => $item['barcode'] ?? null,
                        'color' => $item['color'] ?? null,
                        'image' => $item['image'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '✅ Purchase Created Successfully',
                'redirect' => route('tenant.purchase.show', $purchase->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase Store Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 📄 View All Purchases
     */
    public function index(Request $request)
    {
        $query = Purchase::with('vendor');

        // 🔍 Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_ref', 'LIKE', "%$search%")
                  ->orWhereHas('vendor', function($v) use ($search) {
                      $v->where('name', 'LIKE', "%$search%")
                        ->orWhere('phone', 'LIKE', "%$search%");
                  });
            });
        }

        // 🏷️ Status Filter
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $purchases = $query->latest()->paginate(20)->withQueryString();
        $vendors = \App\Models\Supplier::where('is_active', true)->orderBy('name')->get();
        
        return view('tenant.purchase.index', compact('purchases', 'vendors'));
    }

    /**
     * ✅ Completed Purchase List
     */
    public function completed(Request $request)
    {
        $query = Purchase::with(['vendor'])->where('status', 'Completed');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function($q) use ($term) {
                $q->whereHas('vendor', function($v) use ($term) {
                    $v->where('name', 'ilike', "%{$term}%");
                })->orWhere('invoice_ref', 'ilike', "%{$term}%")
                  ->orWhere('id', 'like', "%{$term}%");
            });
        }

        $purchases = $query->latest()->paginate(20)->withQueryString();
        $vendors = \App\Models\Supplier::where('is_active', true)->orderBy('name')->get();

        return view('tenant.purchase.completed', compact('purchases', 'vendors'));
    }

    /**
     * 📜 Purchase Settlement Module (Multi-view)
     */
    public function settlement(Request $request)
    {
        $view = $request->query('view', 'purchase');
        $query = null;

        if ($view === 'purchase') {
            // 🏷️ View: Purchase (Pending Purchase Orders)
            $query = Purchase::with(['vendor'])->where('status', '!=', 'Completed');
        } elseif ($view === 'settlements') {
            // 💳 View: Settlements (Matches Screenshot 2 - typically general vendor payments)
            $query = \App\Models\PurchaseSettlement::with(['purchase', 'vendor'])
                ->where('entry_type', 'payment');
        } elseif ($view === 'completed') {
            // ✅ View: Completed (Matches Screenshot 3)
            $query = Purchase::with(['vendor'])->where('status', 'Completed');
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function($q) use ($term, $view) {
                if ($view === 'completed') {
                    $q->whereHas('vendor', function($v) use ($term) {
                        $v->where('name', 'ilike', "%{$term}%");
                    })->orWhere('id', 'like', "%{$term}%");
                } else {
                    $q->whereHas('vendor', function($v) use ($term) {
                        $v->where('name', 'ilike', "%{$term}%");
                    })->orWhere('document_number', 'ilike', "%{$term}%")
                      ->orWhereHas('vendor', function($v) use ($term) {
                          $v->where('name', 'ilike', "%{$term}%");
                      });
                }
            });
        }

        $data = $query->latest()->paginate(20)->withQueryString();
        
        $vendors = \App\Models\Supplier::where('is_active', true)
            ->withSum(['purchases as pending_due' => function($q) {
                $q->where('status', '!=', 'Completed');
            }], 'balance_amount')
            ->orderBy('name')
            ->get();

        return view('tenant.purchase.settlement', compact('data', 'vendors', 'view'));
    }

    /**
     * 📥 Export Settlements / Purchases to CSV
     */
    public function exportSettlement(Request $request)
    {
        $view = $request->query('view', 'purchase');
        $query = null;

        if ($view === 'purchase') {
            $query = Purchase::with(['vendor'])->where('status', '!=', 'Completed');
        } elseif ($view === 'settlements') {
            $query = \App\Models\PurchaseSettlement::with(['purchase', 'vendor'])
                ->where('entry_type', 'payment');
        } elseif ($view === 'completed') {
            $query = Purchase::with(['vendor'])->where('status', 'Completed');
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function($q) use ($term, $view) {
                if ($view === 'completed') {
                    $q->whereHas('vendor', fn($v) => $v->where('name', 'ilike', "%{$term}%"))->orWhere('id', 'like', "%{$term}%");
                } else {
                    $q->whereHas('vendor', fn($v) => $v->where('name', 'ilike', "%{$term}%"))
                      ->orWhere('document_number', 'ilike', "%{$term}%")
                      ->orWhereHas('vendor', fn($v) => $v->where('name', 'ilike', "%{$term}%"));
                }
            });
        }

        $data = $query->latest()->get();
        $filename = "{$view}_export_" . date('Y-m-d_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Date', 'Vendor', 'Type', 'Ref/Doc No', 'Amount (Rs)', 'Balance (Rs)'];
        
        $callback = function() use($data, $columns, $view) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $item) {
                $date = \Carbon\Carbon::parse($item->date ?? $item->invoice_date)->format('d-m-Y');
                $vendor = $item->vendor->name ?? 'N/A';
                $type = $view === 'purchase' || $view === 'completed' ? 'Purchase' : ($item->entry_type ?? 'Payment');
                $doc = $item->invoice_number ?? $item->document_number ?? $item->id;
                
                $amount = $view === 'settlements' ? $item->amount : $item->total_amount;
                $balance = $view === 'settlements' ? ($item->purchase->balance_amount ?? 0) : $item->balance_amount;
                
                fputcsv($file, [$date, $vendor, $type, $doc, number_format($amount, 2, '.', ''), number_format($balance, 2, '.', '')]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * 💳 Store Purchase Settlement (JSON API)
     */
    public function storeSettlement(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:suppliers,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|string',
            'date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $remaining_amount = $request->amount;
            $vendor_id = $request->vendor_id;

            // Find pending purchases for this vendor
            $pending_purchases = Purchase::where('vendor_id', $vendor_id)
                ->where('status', '!=', 'Completed')
                ->where('balance_amount', '>', 0)
                ->orderBy('invoice_date', 'asc')
                ->get();

            // Record the main settlement record
            $settlement = \App\Models\PurchaseSettlement::create([
                'purchase_id' => $pending_purchases->first()->id ?? null,
                'vendor_id' => $vendor_id,
                'date' => $request->date,
                'amount' => $request->amount,
                'payment_mode' => $request->payment_mode,
                'document_number' => $request->document_number,
                'description' => $request->description,
                'entry_type' => strtolower($request->type ?? 'payment')
            ]);

            // Distribute payment across pending purchases
            /** @var \App\Models\Purchase $purchase */
            foreach ($pending_purchases as $purchase) {
                if ($remaining_amount <= 0) break;

                $payment_to_apply = min($remaining_amount, $purchase->balance_amount);
                $purchase->balance_amount -= $payment_to_apply;
                $remaining_amount -= $payment_to_apply;

                if ($purchase->balance_amount <= 0) {
                    $purchase->balance_amount = 0;
                    $purchase->status = 'Completed';
                }
                $purchase->save();
            }

            // Save remaining amount as advance balance
            if ($remaining_amount > 0) {
                $vendor = \App\Models\Supplier::find($vendor_id);
                if ($vendor) {
                    $vendor->advance_balance += $remaining_amount;
                    $vendor->save();
                }
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => '✅ Payment recorded successfully.']);
            }

            return redirect()->back()->with('success', '✅ Payment recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', '❌ Error: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * 🔍 Show Single Purchase
     */
    public function show($id)
    {
        $purchase = Purchase::with(['items', 'vendor'])->findOrFail($id);
        return view('tenant.purchase.show', compact('purchase'));
    }

    /**
     * 🖨️ Print Custom Supplier Order
     */
    public function printPO($id)
    {
        $purchase = Purchase::with(['vendor', 'items'])->findOrFail($id);
        $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
        return view('tenant.purchase.po-advanced', compact('purchase', 'settings'));
    }

    /**
     * ✏️ Edit Purchase
     */
    public function edit($id)
    {
        $purchase = Purchase::with(['items', 'vendor'])->findOrFail($id);
        $vendors = \App\Models\Supplier::where('is_active', true)->orderBy('name')->get();
        $products = \App\Models\Category::where('is_active', true)->orderBy('product_name')->get();

        $items = $purchase->items->map(function($item) {
            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'product_type' => $item->product_type,
                'size' => $item->size,
                'brand' => $item->brand,
                'quantity' => (float)$item->quantity,
                'buy_price' => (float)$item->buy_price,
                'total_amount' => (float)$item->total_amount,
                'discount_percent' => (float)$item->discount_percent,
                'discount_amount' => (float)$item->discount_amount,
                'barcode' => $item->barcode,
                'color' => $item->color,
                'image' => $item->image,
                'dealer_price' => (float)$item->dealer_price,
                'showSuggestions' => false
            ];
        });

        return view('tenant.purchase.edit', compact('purchase', 'vendors', 'products', 'items'));
    }

    /**
     * 🔄 Update Purchase
     */
    public function update(Request $request, $id)
    {
        Log::info('Purchase update attempt for ID: ' . $id, $request->all());
        $purchase = Purchase::findOrFail($id);

        DB::beginTransaction();

        try {
            // 🔄 Calculate Totals & Balance
            $items = collect($request->items);
            $subtotal = $items->sum(fn($i) => (float)($i['total_amount'] ?? 0));
            $totalAmount = $request->total_amount ?: $subtotal;
            $paidAmount = $purchase->settlements()->sum('amount');

            // Update Purchase
            $purchase->update([
                'vendor_id' => $request->vendor_id ?: null,
                'invoice_ref' => $request->invoice_ref,
                'invoice_date' => $request->invoice_date,
                'remark' => $request->remark,
                'discount_percent' => $request->discount_percent ?? 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'gst_percent' => $request->gst_percent ?? 0,
                'gst_amount' => $request->gst_amount ?? 0,
                'total_amount' => $totalAmount,
                'balance_amount' => $totalAmount - $paidAmount,
            ]);

            // 📦 Reverse Old Stock
            foreach ($purchase->items as $oldItem) {
                if (!empty($oldItem->barcode)) {
                    $product = \App\Models\Category::where('barcode', $oldItem->barcode)->first();
                    if ($product) {
                        $product->decrement('stock', $oldItem->quantity ?? 0);
                    }
                }
            }

            // Delete old items
            $purchase->items()->delete();

            // Insert new items and apply stock
            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    if (empty($item['product_name'])) continue;

                    $barcode = $item['barcode'] ?? null;
                    if ($barcode) {
                        $product = \App\Models\Category::where('barcode', $barcode)->first();
                        if ($product) {
                            $product->increment('stock', $item['quantity'] ?? 0);
                            if (isset($item['dealer_price'])) $product->update(['dealer_price' => $item['dealer_price']]);
                            if (isset($item['mrp'])) $product->update(['mrp' => $item['mrp']]);

                            // Trigger Auto-Allocation for Pre-Orders
                            if (class_exists(\App\Services\PreOrderService::class)) {
                                \App\Services\PreOrderService::allocateStockForProduct($product->id);
                            }
                        }
                    }

                    $purchase->items()->create([
                        'product_name' => $item['product_name'],
                        'product_type' => $item['product_type'] ?? null,
                        'size' => $item['size'] ?? null,
                        'brand' => $item['brand'] ?? null,
                        'quantity' => $item['quantity'] ?? 0,
                        'buy_price' => $item['buy_price'] ?? 0,
                        'total_amount' => $item['total_amount'] ?? 0,
                        'discount_percent' => $item['discount_percent'] ?? 0,
                        'discount_amount' => $item['discount_amount'] ?? 0,
                        'gst_percent' => $item['gst_percent'] ?? 0,
                        'gst_amount' => $item['gst_amount'] ?? 0,
                        'grand_total' => $item['grand_total'] ?? 0,
                        'profit_percent' => $item['profit_percent'] ?? 0,
                        'profit_amount' => $item['profit_amount'] ?? 0,
                        'mrp' => $item['mrp'] ?? null,
                        'barcode' => $item['barcode'] ?? null,
                        'color' => $item['color'] ?? null,
                        'image' => $item['image'] ?? null,
                        'dealer_price' => $item['dealer_price'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '✅ Updated Successfully',
                'redirect' => route('tenant.purchase.show', $purchase->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase Update Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 📂 Parse Uploaded CSV / TXT / PDF Invoice → return rows as JSON
     *
     * Accepted CSV column order (case-insensitive header match):
     *   product_name | quantity | buy_price | product_type | brand | size | barcode | discount_percent
     *
     * Plain-text/PDF is also accepted. We attempt to extract lines that look like product rows.
     */
    public function parseUpload(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt,text/plain,pdf,jpg,jpeg,png,gif,bmp,webp|max:20480']);

        try {
            $file = $request->file('file');
            $path = $file->getRealPath();
            $extension = strtolower($file->getClientOriginalExtension());
            $lines = [];
            $rawTextForExtraction = '';

            if ($extension === 'pdf') {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf    = $parser->parseFile($path);
                $pages  = $pdf->getPages();
                
                $visuallyReconstructedLines = [];
                foreach ($pages as $page) {
                    if (method_exists($page, 'getDataTm')) {
                        $textXY = $page->getDataTm();
                        $yGroups = [];
                        foreach ($textXY as $item) {
                            $tm = $item[0];
                            $text = trim($item[1]);
                            if ($text === '') continue;
                            
                            $x = round($tm[4], 2);
                            $y = round($tm[5], 1);
                            
                            $foundY = (string)$y;
                            foreach (array_keys($yGroups) as $existingY) {
                                if (abs((float)$existingY - $y) < 3) {
                                    $foundY = $existingY;
                                    break;
                                }
                            }
                            
                            if (!isset($yGroups[$foundY])) $yGroups[$foundY] = [];
                            $yGroups[$foundY][] = ['x' => $x, 'text' => $text];
                        }
                        
                        krsort($yGroups);
                        
                        foreach ($yGroups as $y => $items) {
                            usort($items, function($a, $b) { return $a['x'] <=> $b['x']; });
                            $lineStr = '';
                            $prevX = -100;
                            foreach ($items as $item) {
                                if ($prevX !== -100 && ($item['x'] - $prevX > 10)) {
                                    $lineStr .= ' ';
                                }
                                $lineStr .= $item['text'];
                                $prevX = $item['x'];
                            }
                            $visuallyReconstructedLines[] = trim($lineStr);
                        }
                    } else {
                        $visuallyReconstructedLines = array_merge($visuallyReconstructedLines, explode("\n", $page->getText()));
                    }
                }
                
                $rawTextForExtraction = implode("\n", $visuallyReconstructedLines);
                
                // For the rest of the script, $lines is just the visual lines
                foreach ($visuallyReconstructedLines as $l) {
                    $l = trim($l);
                    if ($l !== '') {
                        if (str_contains($l, ',')) {
                            $lines[] = $l;
                        } else {
                            $l = preg_replace('/\s{2,}/', ',', $l);
                            $lines[] = $l;
                        }
                    }
                }
            } else {
                $raw  = file_get_contents($path);

                // Detect BOM & strip it
                $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
                $rawTextForExtraction = $raw;

                // Normalize line endings
                $lines = preg_split('/\r\n|\r|\n/', trim($raw));
                $lines = array_filter($lines, fn($l) => trim($l) !== '');
                $lines = array_values($lines);
            }

            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://api.ocr.space/parse/image');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                
                $cfile = new \CURLFile($path, $file->getClientMimeType(), $file->getClientOriginalName());
                $post = [
                    'apikey' => 'helloworld',
                    'language' => 'eng',
                    'isTable' => 'true',
                    'file' => $cfile
                ];
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                $result = curl_exec($ch);
                curl_close($ch);
                
                if ($result) {
                    $json = json_decode($result, true);
                    if (!empty($json['ParsedResults'][0]['ParsedText'])) {
                        $raw = $json['ParsedResults'][0]['ParsedText'];
                        file_put_contents(base_path('ocr_debug.json'), json_encode($json, JSON_PRETTY_PRINT));
                        
                        $rawTextForExtraction = $raw;
                        $lines = preg_split('/\r\n|\r|\n/', trim($raw));
                        $lines = array_filter($lines, fn($l) => trim($l) !== '');
                        $lines = array_values($lines);
                    } else {
                        return response()->json(['success' => false, 'message' => 'Image OCR failed to read tabular text from this image. Please manually enter the products or upload as PDF.'], 422);
                    }
                } else {
                    return response()->json(['success' => false, 'message' => 'Image OCR extraction requires a Tesseract engine on the server or an active API connection. Please manually enter the products.'], 422);
                }
            }

            if (count($lines) === 0) {
                return response()->json(['success' => false, 'message' => 'File is empty or no readable text found.'], 422);
            }

            // --- AI Extraction Logic: Find Vendor and Invoice Details ---
            $extractedVendorId = null;
            $extractedInvoiceRef = '';
            
            // 1. Try to extract invoice number (e.g. Invoice No: INV-1001, Bill No: 1234, Invoice: 99)
            if (preg_match_all('/(?:invoice|bill)\s*(?:no\.?|number|ref)?\s*[:#-]?\s*([a-z0-9\-\_\/]+)/i', $rawTextForExtraction, $matches)) {
                foreach ($matches[1] as $match) {
                    $m = strtolower($match);
                    // Must not be a common keyword, must be > 1 char, and MUST contain at least one digit
                    if (!in_array($m, ['invoice', 'no', 'number', 'bill', 'ref', 'date', 'of']) && strlen($m) > 1 && preg_match('/[0-9]/', $m)) {
                        $extractedInvoiceRef = strtoupper($match);
                        break;
                    }
                }
            }

            // Fallback: Just look for anything that looks like a standard invoice ID (e.g. INV202604170019)
            if (!$extractedInvoiceRef) {
                // Must have INV followed by optional dash/underscore/slash, and then MUST start with a digit
                if (preg_match('/\b(INV[\-\_\/]?[0-9][A-Z0-9\/]*)\b/i', $rawTextForExtraction, $matches)) {
                    $extractedInvoiceRef = strtoupper($matches[1]);
                }
            }

            // 2. Try to match Vendor from Database
            $vendors = \App\Models\Supplier::where('is_active', true)->get();
            $normalizedText = preg_replace('/\s+/', ' ', strtolower($rawTextForExtraction));
            foreach ($vendors as $vendor) {
                $normalizedVendor = preg_replace('/\s+/', ' ', strtolower($vendor->name));
                if (strlen($normalizedVendor) > 2 && str_contains($normalizedText, $normalizedVendor)) {
                    $extractedVendorId = $vendor->id;
                    break;
                }
            }

            // ── Detect if first row is a header ──────────────────────────────
            $firstCells = str_getcsv($lines[0]);
            $hasHeader  = !is_numeric(trim($firstCells[0] ?? ''));

            $colMap = []; // colName => index
            $dataStart = 0;

            if ($hasHeader && count($firstCells) > 1) {
                $dataStart = 1;
                foreach ($firstCells as $i => $cell) {
                    $key = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $cell)));
                    
                    if (in_array($key, ['productname', 'product', 'item', 'itemname', 'name', 'description', 'particulars'])) {
                        $colMap['product_name'] = $i;
                    } elseif (in_array($key, ['quantity', 'qty', 'pieces', 'pcs'])) {
                        $colMap['quantity'] = $i;
                    } elseif (in_array($key, ['buyprice', 'price', 'rate', 'unitprice', 'cost', 'amount'])) {
                        $colMap['buy_price'] = $i;
                    } elseif (in_array($key, ['producttype', 'category', 'type'])) {
                        $colMap['product_type'] = $i;
                    } elseif (in_array($key, ['brand', 'make'])) {
                        $colMap['brand'] = $i;
                    } elseif (in_array($key, ['size', 'dimensions'])) {
                        $colMap['size'] = $i;
                    } elseif (in_array($key, ['barcode', 'upc', 'ean', 'sku'])) {
                        $colMap['barcode'] = $i;
                    } elseif (in_array($key, ['discountpercent', 'discount', 'disc', 'discountpct'])) {
                        $colMap['discount_percent'] = $i;
                    }
                }
            }

            // If we couldn't map any columns, consider it unstructured
            $isStructured = count($colMap) > 0;

            // Resolve column index helper
            $col = function(array $row, string $name, int $fallback, $default = '') use ($colMap) {
                if (isset($colMap[$name]) && isset($row[$colMap[$name]])) {
                    return $row[$colMap[$name]];
                }
                // If the fallback index exists and we didn't explicitly map something else there
                if (isset($row[$fallback]) && !in_array($fallback, $colMap)) {
                     return $row[$fallback];
                }
                return $default;
            };

            // State variables across lines for unstructured heuristic parsing
            $pendingProductName = '';
            $pendingNumbers = null;
            $items = [];
            
            for ($i = $dataStart; $i < count($lines); $i++) {
                $rawLine = trim($lines[$i]);
                if (empty($rawLine)) continue;

                // For PDF/TXT or unmapped CSVs, try to extract intelligently from the raw line
                if (!$isStructured) {
                    // Split the line into parts by whitespace only (do not split by comma as numbers use thousand separators)
                    $parts = preg_split('/\s+/', $rawLine);

                    $strings = [];
                    $numbers = [];

                    foreach ($parts as $part) {
                        $clean = trim($part);
                        // Is it a price/qty like number? (e.g. 1, 699.15, 10.00)
                        $cleanNum = str_replace([',', '/-', '/'], '', $clean);
                        if (is_numeric($cleanNum) && (float)$cleanNum >= 0) {
                            $numbers[] = (float)$cleanNum;
                        } else {
                            // If it has letters, it's part of the product string
                            if (preg_match('/[a-zA-Z]/', $clean) || strlen($clean) > 3) {
                                $strings[] = $clean;
                            }
                        }
                    }

                    // Extract name from strings
                    $extractedName = implode(' ', $strings);
                    
                    // Filter out names that are purely units
                    $nameWords = array_filter(explode(' ', strtolower($extractedName)));
                    $units = ['kgs', 'kg', 'pcs', 'nos', 'no', 'ltr', 'mtr', 'box', 'job'];
                    $isOnlyUnits = count($nameWords) > 0 && count(array_diff($nameWords, $units)) === 0;
                    if ($isOnlyUnits) {
                        $extractedName = '';
                    }
                    
                    // Blacklist common non-item rows
                    $lowerName = strtolower($extractedName);
                    $blacklist = ['total', 'subtotal', 'gst', 'cgst', 'sgst', 'igst', 'tax', 'amount', 'balance', 'due', 'bank', 'a/c', 'a/ c', 'account', 'period', 'date', 'invoice', 'code', 'rupee', 'state', 'phone', 'ph:', 'ph ', 'mail', 'cin', 'subject', 'declare', 'declaration', 'terms', 'delivery', 'e.o.e', 'e.&o.e', 'e. & o.e', 'e. o.e', 'only', 'payment', 'value', 'chargeable', 'hsn', 'sac', 'po no', 'round', 'less', 'discount', 'freight'];
                    $skip = false;
                    foreach ($blacklist as $bad) {
                        if (str_contains($lowerName, $bad)) {
                            $skip = true;
                            break;
                        }
                    }
                    
                    if (!$skip && trim($extractedName) !== '') {
                        $pendingProductName = $extractedName;
                    }
                    
                    if ($skip) continue;

                    // Filter out numbers that look like HSN codes (usually 6+ digits without decimals) or years
                    $validNumbers = array_filter($numbers, function($n) {
                        return ($n < 100000 && $n > 0) || strpos((string)$n, '.') !== false;
                    });
                    $validNumbers = array_values($validNumbers);

                    // If we have a pending name and no numbers on this line, but we have pending numbers from the PREVIOUS line
                    if (count($validNumbers) < 2) {
                        if (trim($extractedName) !== '' && $pendingNumbers !== null) {
                            $productName = trim($extractedName);
                            $qty = $pendingNumbers['qty'];
                            $price = $pendingNumbers['price'];
                            
                            $pendingProductName = '';
                            $pendingNumbers = null;
                        } else {
                            continue;
                        }
                    } else {
                        $qty = 0;
                        $price = 0;
                        
                        // Advanced Mathematical Heuristic: Check if A * B = C (Qty * Price = Amount)
                        // This guarantees perfect assignments and can auto-correct OCR decimal typos!
                        if (count($validNumbers) >= 3) {
                            $n1 = $validNumbers[0];
                            $n2 = $validNumbers[1];
                            $n3 = $validNumbers[2];
                            
                            if (abs(($n1 * $n2) - $n3) < 5) {
                                $qty = $n1; $price = $n2;
                            } 
                            // Auto-correct if OCR missed the decimal on the Price (e.g. 37500 instead of 375.00)
                            elseif (abs(($n1 * ($n2 / 100)) - $n3) < 5) {
                                $qty = $n1; $price = $n2 / 100;
                            }
                            // Auto-correct if OCR missed the decimal on the Amount
                            elseif (abs(($n1 * $n2) - ($n3 / 100)) < 5) {
                                $qty = $n1; $price = $n2;
                            }
                        }

                        // If math check failed, fall back to standard decimal heuristic
                        if ($price == 0 || $qty == 0) {
                            foreach ($validNumbers as $idx => $n) {
                                if (strpos((string)$n, '.') !== false) {
                                    $price = $n;
                                    if ($idx > 0 && floor($validNumbers[$idx-1]) == $validNumbers[$idx-1] && $validNumbers[$idx-1] < 1000) {
                                        $qty = $validNumbers[$idx-1];
                                    } elseif (isset($validNumbers[$idx+1]) && floor($validNumbers[$idx+1]) == $validNumbers[$idx+1] && $validNumbers[$idx+1] < 1000) {
                                        $qty = $validNumbers[$idx+1];
                                    }
                                    break;
                                }
                            }
                        }

                        if ($price == 0 || $qty == 0) {
                            $searchNumbers = $validNumbers;
                            if (end($searchNumbers) == 0) array_pop($searchNumbers);
                            
                            $potentials = array_filter($searchNumbers, function($n) { return $n < 100000; });
                            if (count($potentials) >= 2) {
                                $price = max($potentials);
                                $qty = min($potentials);
                                if ($qty > 1000 && strpos((string)$qty, '.') !== false) {
                                    continue;
                                }
                            }
                        }
                        
                        if ($price < 20 && strpos((string)$price, '.') === false) continue;

                        $productName = trim($extractedName) !== '' ? $extractedName : $pendingProductName;
                        if ($productName !== '') {
                            $pendingProductName = '';
                        }

                        if ($productName === '' || $price <= 0 || $qty <= 0) {
                            if ($productName === '' && $price > 0 && $qty > 0) {
                                $pendingNumbers = ['qty' => $qty, 'price' => $price];
                            }
                            continue;
                        }
                    }

                    $productType = 'General';
                    $brand       = 'Default';
                    $size        = '';
                    $barcode     = '';
                    $discountPct = 0;
                } else {
                    $row = str_getcsv($rawLine);
                    if (empty(trim($row[0] ?? ''))) continue;

                    $productName    = trim($col($row, 'product_name', 0, ''));
                    
                    $rawQty         = $col($row, 'quantity', 1, '1');
                    $qty            = floatval(preg_replace('/[^0-9.]/', '', $rawQty));
                    
                    $rawPrice       = $col($row, 'buy_price', 2, '0');
                    $price          = floatval(preg_replace('/[^0-9.]/', '', $rawPrice));
                    
                    $productType    = trim($col($row, 'product_type', 3, 'General'));
                    $brand          = trim($col($row, 'brand', 4, 'Default'));
                    $size           = trim($col($row, 'size', 5, ''));
                    $barcode        = trim($col($row, 'barcode', 6, ''));
                    
                    $rawDisc        = $col($row, 'discount_percent', 7, '0');
                    $discountPct    = floatval(preg_replace('/[^0-9.]/', '', $rawDisc));
                }

                if ($productName === '' || $price <= 0 || $qty <= 0) continue;

                $subtotal       = $qty * $price;
                $discountAmt    = $subtotal * ($discountPct / 100);
                $totalAmt       = $subtotal - $discountAmt;

                // Try to match an existing product by name or barcode for extra data
                $product = null;
                if ($barcode) {
                    $product = \App\Models\Category::where('barcode', $barcode)->first();
                }
                if (!$product) {
                    $product = \App\Models\Category::where('product_name', 'ilike', $productName)->first();
                }

                $taxPct = 0;
                // Attempt to parse % from raw text (OCR)
                if (isset($rawLine) && preg_match('/(\d+(?:\.\d+)?)\s*%/', $rawLine, $m)) {
                    $taxPct = floatval($m[1]);
                } elseif ($product) {
                    $taxPct = floatval($product->gst ?? 0);
                }

                $taxAmt = $totalAmt * ($taxPct / 100);
                $finalTotal = $totalAmt + $taxAmt;

                $items[] = [
                    'product_id'       => $product?->id ?? '',
                    'product_name'     => $productName,
                    'product_type'     => $productType ?: ($product?->product_type ?? 'General'),
                    'brand'            => $brand ?: ($product?->brand ?? 'Default'),
                    'size'             => $size ?: ($product?->size ?? ''),
                    'barcode'          => $barcode ?: ($product?->barcode ?? ''),
                    'color'            => $product?->color ?? '',
                    'image'            => $product?->image ?? '',
                    'dealer_price'     => $product?->dealer_price ?? $price,
                    'quantity'         => $qty,
                    'buy_price'        => $price,
                    'discount_percent' => $discountPct,
                    'discount_amount'  => round($discountAmt, 2),
                    'tax_percent'      => $taxPct,
                    'tax_amount'       => round($taxAmt, 2),
                    'total_amount'     => round($finalTotal, 2),
                    'showSuggestions'  => false,
                ];
            }

            if (count($items) === 0) {
                return response()->json(['success' => false, 'message' => 'No valid products found in the file. Ensure the format is readable.'], 422);
            }

            return response()->json([
                'success' => true, 
                'items' => $items, 
                'count' => count($items),
                'extracted' => [
                    'vendor_id' => $extractedVendorId,
                    'invoice_ref' => $extractedInvoiceRef,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Purchase file parse error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Could not parse file: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 🗑 Delete Purchase
     */
    public function destroy($id)
    {
        $purchase = Purchase::findOrFail($id);
        $purchase->delete();

        return back()->with('success', '🗑 Deleted Successfully');
    }
}
