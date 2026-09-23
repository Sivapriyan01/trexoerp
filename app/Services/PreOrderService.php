<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PreOrderService
{
    /**
     * Automatically allocate stock and update pre-order status to Confirmed.
     * Uses FIFO implicit in oldest first checking of negative stock.
     */
    public static function allocateStockForProduct(int $productId)
    {
        Log::info("PreOrderService: Checking stock allocation for product ID: {$productId}");

        // Find all bills with status 'Awaiting Stock' containing this product
        $bills = Bill::whereIn('status', ['Awaiting Stock', 'draft'])
            ->whereHas('items', function ($query) use ($productId) {
                $query->where('category_id', $productId);
            })
            ->orderBy('id', 'asc') // Oldest first
            ->get();

        foreach ($bills as $bill) {
            $allSatisfied = true;

            foreach ($bill->items as $item) {
                $product = $item->category;
                if ($product && $product->stock < 0) {
                    // Stock is still negative, meaning we haven't satisfied this pre-order yet
                    $allSatisfied = false;
                    break;
                }
            }

            if ($allSatisfied) {
                Log::info("PreOrderService: All items satisfied for Pre-Order ID: {$bill->id}. Updating status to Confirmed.");
                $bill->update([
                    'status' => 'Confirmed',
                    'remarks' => trim(($bill->remarks ?? '') . "\n[System Auto-Allocated Stock on " . date('Y-m-d H:i') . "]")
                ]);
            }
        }
    }

    /**
     * Convert Pre-Order (Draft) to Sales Order / Completed invoice.
     */
    public static function convertToSalesOrder(int $billId, float $collectedAmount = 0, string $paymentMode = 'cash')
    {
        return DB::transaction(function () use ($billId, $collectedAmount, $paymentMode) {
            $bill = Bill::findOrFail($billId);

            $newPaidAmount = $bill->paid_amount + $collectedAmount;
            $newBalance = max(0, $bill->grand_total - $newPaidAmount);
            
            // Determine final status
            $newStatus = $newBalance <= 0 ? 'completed' : 'processing';

            $bill->update([
                'bill_type'    => 'billing', // Change to standard billing invoice
                'paid_amount'  => $newPaidAmount,
                'balance'      => $newBalance,
                'status'       => $newStatus,
                'payment_mode' => strtolower($paymentMode),
                'remarks'      => trim(($bill->remarks ?? '') . "\n[Converted to Sales Order on " . date('Y-m-d H:i') . "]")
            ]);

            // Force recalculation
            $bill->recalculate();

            return [
                'success' => true,
                'message' => 'Pre-Order converted to Sales Order successfully!',
                'bill' => $bill
            ];
        });
    }
}
