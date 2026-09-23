<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLog extends Model
{
    protected $fillable = [
        'category_id',
        'type',
        'quantity',
        'old_stock',
        'new_stock',
        'reference_id',
        'reference_type',
        'remark'
    ];

    public function product()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Log a stock change
     */
    public static function log($productId, $qty, $type, $refId = null, $refType = null, $remark = null)
    {
        $product = Category::find($productId);
        if (!$product) return;

        $oldStock = $product->stock;
        $newStock = ($type === 'in') ? ($oldStock + $qty) : ($oldStock - $qty);

        return self::create([
            'category_id'    => $productId,
            'type'           => $type,
            'quantity'       => $qty,
            'old_stock'      => $oldStock,
            'new_stock'      => $newStock,
            'reference_id'   => $refId,
            'reference_type' => $refType,
            'remark'         => $remark
        ]);
    }
}
