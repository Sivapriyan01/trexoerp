<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryApiController extends Controller
{
    // GET /api/v1/inventory
    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->filled('q')) {
            $query->where('product_name', 'ilike', '%' . $request->q . '%')
                  ->orWhere('barcode', $request->q);
        }

        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock', '<=', 'low_stock_alert');
        }

        if ($request->filled('category')) {
            $query->where('product_type', $request->category);
        }

        $products = $query->orderBy('product_name')->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data'    => $products->items(),
            'meta'    => ['current_page' => $products->currentPage(), 'last_page' => $products->lastPage(), 'total' => $products->total()],
        ]);
    }

    // GET /api/v1/inventory/summary
    public function summary()
    {
        $total      = Category::count();
        $lowStock   = Category::whereColumn('stock', '<=', 'low_stock_alert')->count();
        $outOfStock = Category::where('stock', '<=', 0)->count();
        $totalValue = Category::selectRaw('SUM(stock * dealer_price) as value')->value('value') ?? 0;

        return response()->json([
            'success' => true,
            'data'    => [
                'total_products'  => $total,
                'low_stock'       => $lowStock,
                'out_of_stock'    => $outOfStock,
                'total_value'     => round($totalValue, 2),
            ],
        ]);
    }

    // GET /api/v1/inventory/history
    public function history(Request $request)
    {
        $query = StockLog::with('product')
            ->latest();

        if ($request->filled('product_id')) {
            $query->where('category_id', $request->product_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data'    => $logs->items(),
            'meta'    => ['current_page' => $logs->currentPage(), 'last_page' => $logs->lastPage(), 'total' => $logs->total()],
        ]);
    }

    // PUT /api/v1/inventory/{id}/adjust
    public function adjust(Request $request, $id)
    {
        $product = Category::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $data = $request->validate([
            'qty'    => 'required|numeric',
            'type'   => 'required|in:in,out,adjustment',
            'reason' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($product, $data) {
            if ($data['type'] === 'in') {
                $product->increment('stock', abs($data['qty']));
            } elseif ($data['type'] === 'out') {
                $product->decrement('stock', abs($data['qty']));
            } else {
                $product->update(['stock' => $data['qty']]);
            }

            StockLog::log($product->id, abs($data['qty']), $data['type'], null, $data['reason'] ?? 'manual_adjustment');
        });

        return response()->json(['success' => true, 'data' => $product->fresh(), 'message' => 'Stock adjusted.']);
    }

    // POST /api/v1/inventory/bulk-adjust
    public function bulkAdjust(Request $request)
    {
        $request->validate([
            'items'         => 'required|array|min:1',
            'items.*.id'    => 'required|exists:categories,id',
            'items.*.stock' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->items as $item) {
                $product = Category::find($item['id']);
                if (!$product) continue;
                StockLog::log($product->id, abs($item['stock'] - $product->stock), 'adjustment', null, 'bulk_adjust');
                $product->update(['stock' => $item['stock']]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Bulk stock adjusted.']);
    }

    // GET /api/v1/inventory/{id}
    public function show($id)
    {
        $product = Category::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $recentLogs = StockLog::where('category_id', $id)->latest()->limit(10)->get();

        return response()->json([
            'success' => true,
            'data'    => array_merge($product->toArray(), ['recent_logs' => $recentLogs]),
        ]);
    }

    // GET /api/v1/inventory/low-stock
    public function lowStock(Request $request)
    {
        $products = Category::whereColumn('stock', '<=', 'low_stock_alert')
            ->orderBy('stock')
            ->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data'    => $products->items(),
            'meta'    => ['total' => $products->total(), 'current_page' => $products->currentPage(), 'last_page' => $products->lastPage()],
        ]);
    }

    // GET /api/v1/inventory/categories
    public function categories()
    {
        $categories = Category::select('product_type')->distinct()->whereNotNull('product_type')->pluck('product_type');

        return response()->json(['success' => true, 'data' => $categories]);
    }

    // GET /api/v1/inventory/valuation
    public function valuation(Request $request)
    {
        $products = Category::selectRaw('product_name, stock, dealer_price, (stock * dealer_price) as total_value')
            ->orderByRaw('(stock * dealer_price) desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data'    => $products->items(),
            'meta'    => ['total' => $products->total()],
        ]);
    }
}
