<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseApiController extends Controller
{
    // -------------------------------------------------------------------------
    // GET /api/v1/purchase
    // List all purchases with filters (search, status, date)
    // -------------------------------------------------------------------------
    public function index(Request $request)
    {
        $query = Purchase::with(['vendor', 'items']);

        // Search by invoice ref or vendor name/phone
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('invoice_ref', 'ilike', "%$search%")
                  ->orWhereHas('vendor', function($v) use ($search) {
                      $v->where('name', 'ilike', "%$search%")
                        ->orWhere('phone', 'ilike', "%$search%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by vendor_id
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        // Filter by date range
        if ($request->filled('from')) {
            $query->whereDate('invoice_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('invoice_date', '<=', $request->to);
        }

        $purchases = $query->latest()->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $purchases->items(),
            'meta'    => [
                'current_page' => $purchases->currentPage(),
                'last_page'    => $purchases->lastPage(),
                'per_page'     => $purchases->perPage(),
                'total'        => $purchases->total(),
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/v1/purchase/{id}
    // Show single purchase details
    // -------------------------------------------------------------------------
    public function show($id)
    {
        $purchase = Purchase::with(['items', 'vendor'])->find($id);

        if (!$purchase) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $purchase,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /api/v1/purchase
    // Create/Store a new purchase (Inward Purchase)
    // -------------------------------------------------------------------------
    public function store(Request $request)
    {
        Log::info('API Purchase store attempt', $request->all());

        // Validate incoming request
        $request->validate([
            'invoice_ref' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'vendor_id' => 'nullable|exists:suppliers,id',
            'remark' => 'nullable|string',
            'discount_percent' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'gst_percent' => 'nullable|numeric|min:0',
            'gst_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.product_type' => 'nullable|string',
            'items.*.size' => 'nullable|string',
            'items.*.brand' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.buy_price' => 'required|numeric|min:0',
            'items.*.total_amount' => 'nullable|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.gst_percent' => 'nullable|numeric|min:0',
            'items.*.gst_amount' => 'nullable|numeric|min:0',
            'items.*.grand_total' => 'nullable|numeric|min:0',
            'items.*.profit_percent' => 'nullable|numeric|min:0',
            'items.*.profit_amount' => 'nullable|numeric|min:0',
            'items.*.mrp' => 'nullable|numeric|min:0',
            'items.*.dealer_price' => 'nullable|numeric|min:0',
            'items.*.barcode' => 'nullable|string',
            'items.*.color' => 'nullable|string',
            'items.*.image' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Pre-calculate / map items to compute missing fields
            $items = array_map(function ($item) {
                $qty = (float)($item['quantity'] ?? 0);
                $buyPrice = (float)($item['buy_price'] ?? 0);
                
                // total_amount = qty * buy_price
                if (!isset($item['total_amount']) || (float)$item['total_amount'] == 0) {
                    $item['total_amount'] = $qty * $buyPrice;
                }
                
                // discount_amount
                $discountPercent = (float)($item['discount_percent'] ?? 0);
                if (!isset($item['discount_amount']) || (float)$item['discount_amount'] == 0) {
                    $item['discount_amount'] = $item['total_amount'] * ($discountPercent / 100);
                }
                
                $afterDiscount = $item['total_amount'] - (float)$item['discount_amount'];
                
                // gst_amount
                $gstPercent = (float)($item['gst_percent'] ?? 0);
                if (!isset($item['gst_amount']) || (float)$item['gst_amount'] == 0) {
                    $item['gst_amount'] = $afterDiscount * ($gstPercent / 100);
                }
                
                // grand_total
                if (!isset($item['grand_total']) || (float)$item['grand_total'] == 0) {
                    $item['grand_total'] = $afterDiscount + (float)$item['gst_amount'];
                }
                
                return $item;
            }, $request->items);

            // Calculate totals if not provided
            $itemsCollect = collect($items);
            $subtotal = $itemsCollect->sum(fn($i) => (float)($i['grand_total'] ?? 0));
            $totalAmount = $request->total_amount ?: $subtotal;

            // Create Purchase record
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

            // Save Items & Update Stock
            foreach ($items as $item) {
                if (empty($item['product_name'])) continue;

                $barcode = $item['barcode'] ?? null;
                if ($barcode) {
                    $product = Category::where('barcode', $barcode)->first();
                    if ($product) {
                        $product->increment('stock', $item['quantity'] ?? 0);
                        
                        // Update product prices if provided
                        $priceUpdate = [];
                        if (isset($item['dealer_price'])) $priceUpdate['dealer_price'] = $item['dealer_price'];
                        if (isset($item['mrp'])) $priceUpdate['mrp'] = $item['mrp'];
                        
                        if (!empty($priceUpdate)) {
                            $product->update($priceUpdate);
                        }

                        // Trigger Auto-Allocation for Pre-Orders
                        if (class_exists(\App\Services\PreOrderService::class)) {
                            \App\Services\PreOrderService::allocateStockForProduct($product->id);
                        }
                    }
                }

                $purchase->items()->create([
                    'product_name'     => $item['product_name'],
                    'product_type'     => $item['product_type'] ?? null,
                    'size'             => $item['size'] ?? null,
                    'brand'            => $item['brand'] ?? null,
                    'quantity'         => $item['quantity'] ?? 0,
                    'buy_price'        => $item['buy_price'] ?? 0,
                    'total_amount'     => $item['total_amount'] ?? 0,
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'discount_amount'  => $item['discount_amount'] ?? 0,
                    'gst_percent'      => $item['gst_percent'] ?? 0,
                    'gst_amount'       => $item['gst_amount'] ?? 0,
                    'grand_total'      => $item['grand_total'] ?? 0,
                    'profit_percent'   => $item['profit_percent'] ?? 0,
                    'profit_amount'    => $item['profit_amount'] ?? 0,
                    'mrp'              => $item['mrp'] ?? null,
                    'dealer_price'     => $item['dealer_price'] ?? null,
                    'barcode'          => $item['barcode'] ?? null,
                    'color'            => $item['color'] ?? null,
                    'image'            => $item['image'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase created successfully.',
                'data' => [
                    'id'           => $purchase->id,
                    'invoice_ref'  => $purchase->invoice_ref,
                    'total_amount' => $purchase->total_amount,
                    'status'       => $purchase->status,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Purchase Store Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // PUT /api/v1/purchase/{id}
    // Update purchase details & update inventory stock
    // -------------------------------------------------------------------------
    public function update(Request $request, $id)
    {
        Log::info('API Purchase update attempt for ID: ' . $id, $request->all());
        $purchase = Purchase::with('items')->find($id);

        if (!$purchase) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase not found.'
            ], 404);
        }

        $request->validate([
            'invoice_ref' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'vendor_id' => 'nullable|exists:suppliers,id',
            'remark' => 'nullable|string',
            'discount_percent' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'gst_percent' => 'nullable|numeric|min:0',
            'gst_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.product_type' => 'nullable|string',
            'items.*.size' => 'nullable|string',
            'items.*.brand' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.buy_price' => 'required|numeric|min:0',
            'items.*.total_amount' => 'nullable|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.gst_percent' => 'nullable|numeric|min:0',
            'items.*.gst_amount' => 'nullable|numeric|min:0',
            'items.*.grand_total' => 'nullable|numeric|min:0',
            'items.*.profit_percent' => 'nullable|numeric|min:0',
            'items.*.profit_amount' => 'nullable|numeric|min:0',
            'items.*.mrp' => 'nullable|numeric|min:0',
            'items.*.dealer_price' => 'nullable|numeric|min:0',
            'items.*.barcode' => 'nullable|string',
            'items.*.color' => 'nullable|string',
            'items.*.image' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Pre-calculate / map items to compute missing fields
            $items = array_map(function ($item) {
                $qty = (float)($item['quantity'] ?? 0);
                $buyPrice = (float)($item['buy_price'] ?? 0);
                
                // total_amount = qty * buy_price
                if (!isset($item['total_amount']) || (float)$item['total_amount'] == 0) {
                    $item['total_amount'] = $qty * $buyPrice;
                }
                
                // discount_amount
                $discountPercent = (float)($item['discount_percent'] ?? 0);
                if (!isset($item['discount_amount']) || (float)$item['discount_amount'] == 0) {
                    $item['discount_amount'] = $item['total_amount'] * ($discountPercent / 100);
                }
                
                $afterDiscount = $item['total_amount'] - (float)$item['discount_amount'];
                
                // gst_amount
                $gstPercent = (float)($item['gst_percent'] ?? 0);
                if (!isset($item['gst_amount']) || (float)$item['gst_amount'] == 0) {
                    $item['gst_amount'] = $afterDiscount * ($gstPercent / 100);
                }
                
                // grand_total
                if (!isset($item['grand_total']) || (float)$item['grand_total'] == 0) {
                    $item['grand_total'] = $afterDiscount + (float)$item['gst_amount'];
                }
                
                return $item;
            }, $request->items);

            $itemsCollect = collect($items);
            $subtotal = $itemsCollect->sum(fn($i) => (float)($i['grand_total'] ?? 0));
            $totalAmount = $request->total_amount ?: $subtotal;
            $paidAmount = $purchase->settlements()->sum('amount');

            // 📦 Reverse Old Stock
            foreach ($purchase->items as $oldItem) {
                if (!empty($oldItem->barcode)) {
                    $product = Category::where('barcode', $oldItem->barcode)->first();
                    if ($product) {
                        $product->decrement('stock', $oldItem->quantity ?? 0);
                    }
                }
            }

            // Delete old items
            $purchase->items()->delete();

            // Update Purchase record
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

            // Save new items and apply stock
            foreach ($items as $item) {
                if (empty($item['product_name'])) continue;

                $barcode = $item['barcode'] ?? null;
                if ($barcode) {
                    $product = Category::where('barcode', $barcode)->first();
                    if ($product) {
                        $product->increment('stock', $item['quantity'] ?? 0);
                        
                        $priceUpdate = [];
                        if (isset($item['dealer_price'])) $priceUpdate['dealer_price'] = $item['dealer_price'];
                        if (isset($item['mrp'])) $priceUpdate['mrp'] = $item['mrp'];
                        
                        if (!empty($priceUpdate)) {
                            $product->update($priceUpdate);
                        }

                        // Trigger Auto-Allocation for Pre-Orders
                        if (class_exists(\App\Services\PreOrderService::class)) {
                            \App\Services\PreOrderService::allocateStockForProduct($product->id);
                        }
                    }
                }

                $purchase->items()->create([
                    'product_name'     => $item['product_name'],
                    'product_type'     => $item['product_type'] ?? null,
                    'size'             => $item['size'] ?? null,
                    'brand'            => $item['brand'] ?? null,
                    'quantity'         => $item['quantity'] ?? 0,
                    'buy_price'        => $item['buy_price'] ?? 0,
                    'total_amount'     => $item['total_amount'] ?? 0,
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'discount_amount'  => $item['discount_amount'] ?? 0,
                    'gst_percent'      => $item['gst_percent'] ?? 0,
                    'gst_amount'       => $item['gst_amount'] ?? 0,
                    'grand_total'      => $item['grand_total'] ?? 0,
                    'profit_percent'   => $item['profit_percent'] ?? 0,
                    'profit_amount'    => $item['profit_amount'] ?? 0,
                    'mrp'              => $item['mrp'] ?? null,
                    'dealer_price'     => $item['dealer_price'] ?? null,
                    'barcode'          => $item['barcode'] ?? null,
                    'color'            => $item['color'] ?? null,
                    'image'            => $item['image'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase updated successfully.',
                'data' => [
                    'id'           => $purchase->id,
                    'invoice_ref'  => $purchase->invoice_ref,
                    'total_amount' => $purchase->total_amount,
                    'status'       => $purchase->status,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Purchase Update Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // DELETE /api/v1/purchase/{id}
    // Delete/Cancel a purchase order (does not reverse stock by default, matching Web UI)
    // -------------------------------------------------------------------------
    public function destroy($id)
    {
        $purchase = Purchase::find($id);

        if (!$purchase) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase not found.'
            ], 404);
        }

        try {
            $purchase->delete();

            return response()->json([
                'success' => true,
                'message' => 'Purchase deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete purchase: ' . $e->getMessage()
            ], 500);
        }
    }
}
