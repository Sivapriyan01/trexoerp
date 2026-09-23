<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductMasterApiController extends Controller
{
    // GET /api/v1/products/list   (full product master with cost & details)
    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->filled('q')) {
            $query->where('product_name', 'ilike', '%' . $request->q . '%')
                  ->orWhere('barcode', $request->q)
                  ->orWhere('sku', 'ilike', '%' . $request->q . '%');
        }
        if ($request->filled('category')) {
            $query->where('product_type', $request->category);
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $products = $query->orderBy('product_name')->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data'    => $products->items(),
            'meta'    => ['current_page' => $products->currentPage(), 'last_page' => $products->lastPage(), 'total' => $products->total()],
        ]);
    }

    // POST /api/v1/products/master
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_name'  => 'required|string|max:255',
            'barcode'       => 'nullable|string|max:100|unique:categories,barcode',
            'sku'           => 'nullable|string|max:100',
            'mrp'           => 'required|numeric|min:0',
            'dealer_price'  => 'nullable|numeric|min:0',
            'stock'         => 'nullable|integer|min:0',
            'low_stock_alert' => 'nullable|integer|min:0',
            'unit'          => 'nullable|string|max:50',
            'product_type'  => 'nullable|string|max:100',
            'brand'         => 'nullable|string|max:100',
            'size'          => 'nullable|string|max:100',
            'gst_percent'   => 'nullable|numeric|min:0|max:100',
            'is_active'     => 'nullable|boolean',
            'description'   => 'nullable|string',
        ]);

        $data['is_active'] = $data['is_active'] ?? true;
        $data['stock']     = $data['stock'] ?? 0;

        $product = Category::create($data);

        if (($data['stock'] ?? 0) > 0) {
            StockLog::log($product->id, $data['stock'], 'in', null, 'initial_stock');
        }

        return response()->json(['success' => true, 'data' => $product, 'message' => 'Product created.'], 201);
    }

    // GET /api/v1/products/master/{id}
    public function show($id)
    {
        $product = Category::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $recentLogs = StockLog::where('category_id', $id)->latest()->limit(5)->get();

        return response()->json([
            'success' => true,
            'data'    => array_merge($product->toArray(), ['recent_stock_logs' => $recentLogs]),
        ]);
    }

    // PUT /api/v1/products/master/{id}
    public function update(Request $request, $id)
    {
        $product = Category::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $data = $request->validate([
            'product_name'  => 'sometimes|string|max:255',
            'barcode'       => 'sometimes|nullable|string|max:100|unique:categories,barcode,' . $id,
            'mrp'           => 'sometimes|numeric|min:0',
            'dealer_price'  => 'sometimes|nullable|numeric|min:0',
            'low_stock_alert' => 'sometimes|nullable|integer|min:0',
            'unit'          => 'sometimes|nullable|string|max:50',
            'product_type'  => 'sometimes|nullable|string|max:100',
            'brand'         => 'sometimes|nullable|string|max:100',
            'gst_percent'   => 'sometimes|nullable|numeric|min:0|max:100',
            'is_active'     => 'sometimes|boolean',
        ]);

        $product->update($data);

        return response()->json(['success' => true, 'data' => $product]);
    }

    // DELETE /api/v1/products/master/{id}
    public function destroy($id)
    {
        $product = Category::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $product->update(['is_active' => false]);  // Soft-disable, don't hard-delete (affects bill history)

        return response()->json(['success' => true, 'message' => 'Product deactivated.']);
    }

    // GET /api/v1/products/categories
    public function categories()
    {
        $cats = Category::select('product_type')->distinct()->whereNotNull('product_type')->orderBy('product_type')->pluck('product_type');
        return response()->json(['success' => true, 'data' => $cats]);
    }
}
