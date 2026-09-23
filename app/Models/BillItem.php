<?php
// app/Models/BillItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillItem extends Model
{
    protected $fillable = [
        'bill_id','category_id','barcode','product_name',
        'brand','product_type','model','size','hsn',
        'mrp','quantity','total','is_manual',
    ];

    protected $casts = [
        'mrp'       => 'float',
        'total'     => 'float',
        'is_manual' => 'boolean',
    ];

    public function bill()     { return $this->belongsTo(Bill::class); }
    public function category() { return $this->belongsTo(Category::class); }

    // Auto-calc total on save
    protected static function booted(): void
    {
        static::saving(function ($item) {
            $item->total = round($item->mrp * $item->quantity, 2);
        });
    }
}
