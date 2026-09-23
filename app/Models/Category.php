<?php
// app/Models/Category.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'barcode','product_name','brand','product_type',
        'model','size','hsn','mrp','dealer_price',
        'gst','cgst','sgst','stock','low_stock_alert',
        'color','image','is_active', 'default_processes',
        'pre_order_available', 'expiry_date', 'show_on_website', 'discount_percent',
    ];

    protected $casts = [
        'mrp' => 'float', 
        'dealer_price' => 'float',
        'discount_percent' => 'float',
        'gst' => 'float', 
        'cgst' => 'float', 
        'sgst' => 'float',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'show_on_website' => 'boolean',
        'pre_order_available' => 'boolean',
        'default_processes' => 'array'
    ];

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('product_name', 'ilike', "%{$term}%")
              ->orWhere('barcode',      'ilike', "%{$term}%")
              ->orWhere('brand',        'ilike', "%{$term}%");
        });
    }
}
