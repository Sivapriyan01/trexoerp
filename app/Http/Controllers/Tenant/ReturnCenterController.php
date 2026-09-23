<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReturnCenterController extends Controller
{
    public function index()
    {
        $rmas = \App\Models\RmaRequest::with('bill')->latest()->get();
        
        $kpi = [
            'total_rmas' => \App\Models\RmaRequest::count(),
            'pending_rmas' => \App\Models\RmaRequest::whereIn('status', ['pending', 'inspecting'])->count(),
            'replacements' => \App\Models\RmaRequest::where('type', 'replacement')->count(),
            'processed' => \App\Models\RmaRequest::where('status', 'processed')->count(),
        ];
        
        return view('tenant.returns.index', compact('rmas', 'kpi'));
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'return');
        $invoiceNo = $request->get('invoice_no');

        $bill = \App\Models\Bill::with('items.category')->where('invoice_no', $invoiceNo)->first();

        if (!$bill) {
            return redirect()->route('tenant.returns.index')->with('error', 'Invoice not found: ' . $invoiceNo);
        }

        // Calculate returned/exchanged quantity for each item
        foreach ($bill->items as $item) {
            $alreadyReturned = \App\Models\RmaRequestItem::where('bill_item_id', $item->id)
                ->whereHas('rmaRequest', function ($q) {
                    $q->where('status', '!=', 'rejected');
                })->sum('quantity');
                
            $item->available_qty = max(0, $item->quantity - $alreadyReturned);
        }

        return view('tenant.returns.create', compact('bill', 'type'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bill_id' => 'required|exists:bills,id',
            'type' => 'required|string',
            'items' => 'required|array',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $bill = \App\Models\Bill::findOrFail($request->bill_id);
        
        $selectedCount = 0;
        foreach ($request->items as $itemId => $itemData) {
            if (!empty($itemData['selected']) && $itemData['selected'] == 1) {
                $selectedCount++;
            }
        }
        
        if ($selectedCount === 0) {
            return back()->with('error', 'You must select at least one item to return or exchange.');
        }

        $rmaNumber = 'RMA-' . date('Ymd') . '-' . rand(1000, 9999);
        
        $rma = \App\Models\RmaRequest::create([
            'rma_number' => $rmaNumber,
            'bill_id' => $bill->id,
            'customer_id' => $bill->customer_id,
            'type' => $request->type,
            'status' => 'pending',
            'reason' => $request->reason,
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        foreach ($request->items as $itemId => $itemData) {
            if (!empty($itemData['selected']) && $itemData['selected'] == 1) {
                $billItem = \App\Models\BillItem::findOrFail($itemId);
                
                \App\Models\RmaRequestItem::create([
                    'rma_request_id' => $rma->id,
                    'bill_item_id' => $billItem->id,
                    'category_id' => $billItem->category_id,
                    'quantity' => $itemData['quantity'],
                    'condition' => $itemData['condition'],
                ]);
            }
        }

        return redirect()->route('tenant.returns.index')->with('success', 'RMA Request Created Successfully: ' . $rmaNumber);
    }

    public function show($id)
    {
        $rma = \App\Models\RmaRequest::with(['bill.customer', 'items.billItem', 'items.category', 'customer'])->findOrFail($id);
        return view('tenant.returns.show', compact('rma'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $rma = \App\Models\RmaRequest::with(['items.category', 'items.billItem', 'bill'])->findOrFail($id);
        
        // Prevent processing if no items are approved
        if ($request->status === 'processed') {
            $approvedCount = $rma->items->where('inspection_status', 'approved')->count();
            if ($approvedCount === 0) {
                return back()->with('error', 'Cannot process RMA: No items have been approved. Please inspect and approve items first.');
            }
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $rma) {
            $rma->status = $request->status;
            if ($request->notes) {
                $rma->notes = $rma->notes . "\n[" . now()->format('Y-m-d H:i') . "] " . $request->notes;
            }
            $rma->save();

            // If processed and it's a return, update inventory and financials (Phase 3)
            if ($request->status === 'processed') {
                $creditNoteAmount = 0;
                $hasApprovedItems = false;
                
                // Create Credit Note (Negative Invoice)
                $creditNote = new \App\Models\Bill();
                $creditNote->invoice_no = \App\Models\Bill::generateInvoiceNo($rma->type === 'return' ? 'credit_note' : $rma->type);
                $creditNote->bill_type = $rma->type === 'return' ? 'credit_note' : $rma->type;
                $creditNote->customer_id = $rma->bill->customer_id;
                $creditNote->customer_name = $rma->bill->customer_name;
                $creditNote->customer_phone = $rma->bill->customer_phone;
                $creditNote->bill_date = now();
                $creditNote->status = 'completed';
                $creditNote->payment_mode = 'wallet'; // Or whatever refund method
                $creditNote->created_by = auth()->id();
                $creditNote->remarks = "Auto-generated for RMA: {$rma->rma_number}";
                $creditNote->save();

                foreach ($rma->items as $item) {
                    if ($item->inspection_status === 'approved') {
                        $hasApprovedItems = true;
                        $product = \App\Models\Category::find($item->category_id);
                        
                        // Inventory Adjustment (Restock if not damaged/scrap)
                        if ($product && $item->condition !== 'damaged') {
                            $product->increment('stock', $item->quantity);
                            \App\Models\StockLog::log(
                                $product->id,
                                $item->quantity,
                                'in',
                                $rma->id,
                                'RMA',
                                'RMA Processed: ' . $rma->rma_number
                            );
                        }
                        
                        // Financial Adjustment
                        if ($item->billItem) {
                            $itemTotal = $item->quantity * $item->billItem->mrp;
                            $creditNoteAmount += $itemTotal;
                            
                            \App\Models\BillItem::create([
                                'bill_id' => $creditNote->id,
                                'category_id' => $item->category_id,
                                'product_name' => $item->billItem->product_name,
                                'quantity' => $item->quantity,
                                'mrp' => $item->billItem->mrp,
                                'total' => $itemTotal,
                            ]);
                        }
                    }
                }
                
                if ($hasApprovedItems) {
                    $creditNote->subtotal = $creditNoteAmount;
                    $creditNote->grand_total = $creditNoteAmount;
                    $creditNote->paid_amount = $creditNoteAmount; // Refunded
                    $creditNote->balance = 0;
                    $creditNote->save();
                    
                    // Add to customer wallet balance ONLY if it's a return, NOT an exchange
                    if ($creditNote->customer_id && $rma->type === 'return') {
                        \App\Models\Customer::where('id', $creditNote->customer_id)->increment('wallet_balance', $creditNoteAmount);
                        $rma->notes = $rma->notes . "\n[" . now()->format('Y-m-d H:i') . "] Auto-generated Credit Note: " . $creditNote->invoice_no . " for amount: " . $creditNoteAmount . " (Added to Wallet)";
                    } else if ($rma->type === 'exchange') {
                        $rma->notes = $rma->notes . "\n[" . now()->format('Y-m-d H:i') . "] Auto-generated Exchange Credit Note: " . $creditNote->invoice_no . " for amount: " . $creditNoteAmount . " (Pending Exchange)";
                    }

                    // For Replacement, automatically generate the Replacement Sales Order!
                    if ($rma->type === 'replacement') {
                        $replacementBill = new \App\Models\Bill();
                        $replacementBill->invoice_no = \App\Models\Bill::generateInvoiceNo('replacement');
                        $replacementBill->bill_type = 'replacement';
                        $replacementBill->customer_id = $rma->bill->customer_id;
                        $replacementBill->customer_name = $rma->bill->customer_name;
                        $replacementBill->customer_phone = $rma->bill->customer_phone;
                        $replacementBill->bill_date = now();
                        $replacementBill->status = 'completed';
                        $replacementBill->payment_mode = 'replacement';
                        $replacementBill->created_by = auth()->id();
                        $replacementBill->remarks = "Auto-generated Replacement Sales Order for RMA: {$rma->rma_number} [Credit Note Used: {$creditNote->invoice_no}]";
                        $replacementBill->save();

                        foreach ($rma->items as $item) {
                            if ($item->inspection_status === 'approved') {
                                $product = \App\Models\Category::find($item->category_id);
                                if ($product && $item->billItem) {
                                    $itemTotal = $item->quantity * $item->billItem->mrp;
                                    
                                    \App\Models\BillItem::create([
                                        'bill_id' => $replacementBill->id,
                                        'category_id' => $item->category_id,
                                        'product_name' => $item->billItem->product_name,
                                        'quantity' => $item->quantity,
                                        'mrp' => $item->billItem->mrp,
                                        'total' => $itemTotal,
                                    ]);

                                    // Deduct Stock for the new item being given!
                                    $product->decrement('stock', $item->quantity);
                                    \App\Models\StockLog::log(
                                        $product->id,
                                        $item->quantity,
                                        'out',
                                        $replacementBill->id,
                                        'bill',
                                        'Replacement Given'
                                    );
                                }
                            }
                        }
                        
                        $replacementBill->subtotal = $creditNoteAmount;
                        $replacementBill->grand_total = $creditNoteAmount;
                        $replacementBill->paid_amount = $creditNoteAmount;
                        $replacementBill->balance = 0;
                        $replacementBill->save();
                        
                        $rma->notes = $rma->notes . "\n[" . now()->format('Y-m-d H:i') . "] Auto-generated Credit Note: {$creditNote->invoice_no}\n[" . now()->format('Y-m-d H:i') . "] Automatically fulfilled via Replacement Invoice: {$replacementBill->invoice_no}";
                        $rma->status = 'completed'; // Auto-complete
                    }

                    $rma->save();
                } else {
                    $creditNote->delete(); // Nothing was approved
                }
            }
        });

        return redirect()->route('tenant.returns.show', $rma->id)->with('success', 'RMA Status Updated to ' . ucfirst($request->status));
    }
    public function getCustomerInvoices(Request $request)
    {
        $phone = $request->input('phone');
        if (!$phone || strlen($phone) < 10) {
            return response()->json(['invoices' => []]);
        }
        
        $query = \App\Models\Bill::where('customer_phone', 'LIKE', "%{$phone}%")
            ->whereIn('bill_type', ['billing', 'quick', 'exchange', 'replacement']);
            
        if ($request->has('month') && $request->month) {
            // $request->month is in format 'YYYY-MM'
            $parts = explode('-', $request->month);
            if (count($parts) === 2) {
                $query->whereYear('bill_date', $parts[0])
                      ->whereMonth('bill_date', $parts[1]);
            }
        }
        
        $invoices = $query->orderBy('bill_date', 'desc')
            ->limit(15) // increased limit since we are filtering
            ->get(['invoice_no', 'bill_date', 'grand_total']);
            
        return response()->json(['invoices' => $invoices]);
    }

    public function updateItemInspection(Request $request, $id, $itemId)
    {
        $request->validate([
            'inspection_status' => 'required|in:approved,rejected',
        ]);

        $rmaItem = \App\Models\RmaRequestItem::where('rma_request_id', $id)->findOrFail($itemId);
        $rmaItem->inspection_status = $request->inspection_status;
        $rmaItem->save();

        return redirect()->route('tenant.returns.show', $id)->with('success', 'Item Inspection Status Updated.');
    }
}
