<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Category;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    public function index()
    {
        $branches = Branch::where('is_active', true)->get();
        $stockHealth = [
            'total' => Category::count(),
            'low' => Category::where('stock', '>', 0)->where('stock', '<=', 10)->count(),
            'out' => Category::where('stock', '<=', 0)->count(),
        ];
        $stockHealth['percentage'] = $stockHealth['total'] > 0 ? round((($stockHealth['total'] - $stockHealth['out']) / $stockHealth['total']) * 100) : 100;

        return view('tenant.stock_transfer.index', compact('branches', 'stockHealth'));
    }

    /**
     * Display all stock transfers
     */
    public function list()
    {
        $transfers = StockTransfer::with(['fromBranch', 'toBranch', 'creator'])
            ->latest()
            ->paginate(20);

        return view('tenant.stock_transfer.list', compact('transfers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_branch_id' => 'nullable|exists:branches,id',
            'to_branch_id'   => 'nullable|exists:branches,id',
            'transfer_date'  => 'required|date',
            'type'           => 'required|in:inward,outward',
            'items'          => 'required|array|min:1',
            'items.*.id'     => 'required|exists:categories,id',
            'items.*.qty'    => 'required|numeric|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            $transfer = StockTransfer::create([
                'from_branch_id' => $request->from_branch_id,
                'to_branch_id'   => $request->to_branch_id,
                'transfer_date'  => $request->transfer_date,
                'type'           => $request->type,
                'remark'         => $request->remark,
                'created_by'     => auth()->id(),
            ]);

            $totalQty = 0;
            $totalAmt = 0;

            foreach ($request->items as $item) {
                $product = Category::find($item['id']);
                
                if (!$product) {
                    continue; // Safety check
                }
                
                $transferItem = StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'category_id'       => $product->id,
                    'product_name'      => $product->product_name,
                    'barcode'           => $product->barcode,
                    'brand'             => $product->brand,
                    'size'              => $product->size,
                    'quantity'          => $item['qty'],
                    'price'             => $product->mrp,
                    'total'             => $item['qty'] * $product->mrp,
                ]);

                $totalQty += $item['qty'];
                $totalAmt += $transferItem->total;

                // Adjust Stock based on type
                if ($request->type === 'outward') {
                    StockLog::log($product->id, $item['qty'], 'out', $transfer->id, 'transfer');
                    $product->decrement('stock', $item['qty']);
                } else {
                    StockLog::log($product->id, $item['qty'], 'in', $transfer->id, 'transfer');
                    $product->increment('stock', $item['qty']);
                }
            }

            $transfer->update([
                'total_qty'    => $totalQty,
                'total_amount' => $totalAmt,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stock Transfer recorded successfully!',
                'transfer_id' => $transfer->id
            ]);
        });
    }

    public function report(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $query = StockTransfer::with(['fromBranch', 'toBranch'])
            ->whereBetween('transfer_date', [$startDate, $endDate]);

        $transfers = $query->latest()->paginate(20);

        $stats = [
            'total_transfers' => $query->count(),
            'total_qty'       => $query->sum('total_qty'),
            'total_value'     => $query->sum('total_amount'),
            'inward_count'    => $query->clone()->where('type', 'inward')->count(),
            'outward_count'   => $query->clone()->where('type', 'outward')->count(),
        ];

        return view('tenant.stock_transfer.report', compact('transfers', 'stats', 'startDate', 'endDate'));
    }
}
