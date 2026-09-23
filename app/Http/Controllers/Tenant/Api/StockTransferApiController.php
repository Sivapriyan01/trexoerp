<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Category;
use App\Models\Branch;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransferApiController extends Controller
{
    // GET /api/v1/stock-transfer
    public function index(Request $request)
    {
        $query = StockTransfer::with(['fromBranch', 'toBranch', 'items'])->latest();

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $transfers = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $transfers->items(),
            'meta' => ['current_page' => $transfers->currentPage(), 'last_page' => $transfers->lastPage(), 'total' => $transfers->total()],
        ]);
    }

    // POST /api/v1/stock-transfer
    public function store(Request $request)
    {
        $data = $request->validate([
            'from_branch' => 'required|exists:branches,id',
            'to_branch' => 'required|exists:branches,id|different:from_branch',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:categories,id',
            'items.*.qty' => 'required|numeric|min:1',
        ]);

        DB::transaction(function () use ($data, &$transfer) {
            $transfer = StockTransfer::create([
                'from_branch_id' => $data['from_branch'],
                'to_branch_id' => $data['to_branch'],
                'transfer_date' => now(),
                'notes' => $data['notes'] ?? null,
                'transferred_by' => auth()->id(),
            ]);

            foreach ($data['items'] as $item) {
                $product = Category::find($item['product_id']);
                if (!$product)
                    continue;

                $price = $product->mrp ?? 0;
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'category_id' => $product->id,
                    'product_name' => $product->product_name,
                    'quantity' => $item['qty'],
                    'price' => $price,
                    'total' => $price * $item['qty'],
                ]);

                $product->decrement('stock', $item['qty']);
                StockLog::log($product->id, $item['qty'], 'out', $transfer->id, 'stock_transfer');
            }
        });

        return response()->json(['success' => true, 'data' => $transfer->load('items'), 'message' => 'Stock transferred.'], 201);
    }

    // GET /api/v1/stock-transfer/{id}
    public function show($id)
    {
        $transfer = StockTransfer::with(['fromBranch', 'toBranch', 'items.category'])->find($id);

        if (!$transfer) {
            return response()->json(['success' => false, 'message' => 'Transfer not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $transfer]);
    }

    // GET /api/v1/stock-transfer/report
    public function report(Request $request)
    {
        $query = StockTransfer::with(['fromBranch', 'toBranch', 'items.category']);

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $transfers = $query->get();

        return response()->json([
            'success' => true,
            'data' => $transfers,
            'summary' => [
                'total_transfers' => $transfers->count(),
                'total_items_moved' => $transfers->flatMap(fn($t) => $t->items)->sum('quantity'),
            ],
        ]);
    }
}
