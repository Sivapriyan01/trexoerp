<?php

namespace App\Imports;

use App\Models\Category;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $rowArray = $row->toArray();
        $values = array_values($rowArray);

        // Try standard headers first, fallback to positional columns (0=Name, 1=Stock, 2=Price)
        $productName = $rowArray['product_name'] ?? $rowArray['product'] ?? $rowArray['name'] ?? $rowArray['item'] ?? $rowArray['item_name'] ?? $values[0] ?? null;
        
        // Skip rows that look like titles or empty rows
        if (empty($productName) || in_array(strtolower($productName), ['grand total', 'total', 'item name', 'product name', 'particulars'])) {
            Log::info("Skipped row $rowIndex: Invalid or empty product name.", ['row' => $rowArray]);
            return;
        }

        // Try to get price
        $mrp = $rowArray['mrp'] ?? $rowArray['price'] ?? $rowArray['rate'] ?? $values[2] ?? 0;
        
        // Try to get stock
        $stockToAdd = $rowArray['stock'] ?? $rowArray['qty'] ?? $rowArray['quantity'] ?? $values[1] ?? 0;
        $stockToAdd = (int) $stockToAdd;
        
        $barcode = $rowArray['barcode'] ?? null;

        $gst = isset($rowArray['gst']) ? (float) $rowArray['gst'] : 0;
        $cgst = $gst / 2;
        $sgst = $gst / 2;

        // Try to find existing product by barcode or product name
        $query = Category::query();
        if (!empty($barcode)) {
            $query->where('barcode', $barcode);
        } else {
            $query->where('product_name', $productName);
        }
        
        $product = $query->first();

        if ($product) {
            // Update stock and other fields if provided
            $product->stock += $stockToAdd;
            
            if (!empty($mrp)) {
                $product->mrp = (float) $mrp;
            }
            if (isset($rowArray['dealer_price']) || isset($rowArray['cost'])) {
                $product->dealer_price = (float) ($rowArray['dealer_price'] ?? $rowArray['cost']);
            }
            if (isset($rowArray['brand'])) $product->brand = $rowArray['brand'];
            if (isset($rowArray['expiry_date'])) $product->expiry_date = $rowArray['expiry_date'];
            
            $product->save();
            Log::info("Updated product stock: $productName by $stockToAdd", ['row' => $rowArray]);
        } else {
            // Create new product
            Category::create([
                'product_name'    => $productName,
                'barcode'         => $barcode,
                'brand'           => $rowArray['brand'] ?? null,
                'product_type'    => $rowArray['product_type'] ?? $rowArray['type'] ?? null,
                'model'           => $rowArray['model'] ?? null,
                'size'            => $rowArray['size'] ?? null,
                'hsn'             => $rowArray['hsn'] ?? null,
                'mrp'             => (float) $mrp,
                'dealer_price'    => (float) ($rowArray['dealer_price'] ?? $rowArray['cost'] ?? $mrp),
                'gst'             => $gst,
                'cgst'            => $cgst,
                'sgst'            => $sgst,
                'stock'           => $stockToAdd,
                'low_stock_alert' => isset($rowArray['low_stock_alert']) ? (int) $rowArray['low_stock_alert'] : 5,
                'color'           => $rowArray['color'] ?? null,
                'expiry_date'     => $rowArray['expiry_date'] ?? null,
                'is_active'       => true,
            ]);
            Log::info("Created new product: $productName", ['row' => $rowArray]);
        }
    }
}
