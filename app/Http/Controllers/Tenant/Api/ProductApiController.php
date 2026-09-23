<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    public function index(Request $request)
    {
        // Example logic: fetch products
        // Optional: filter by branch if needed, or search query
        $query = Category::where('is_active', true);

        if ($request->has('q')) {
            $query->where('product_name', 'like', '%' . $request->q . '%')
                  ->orWhere('barcode', $request->q);
        }

        $products = $query->paginate(50);

        return response()->json([
            'success' => true,
            'message' => 'Products fetched successfully',
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total()
            ]
        ], 200);
    }

    public function show($id)
    {
        $product = Category::find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $product], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'barcode' => 'nullable|string|unique:categories,barcode',
            'mrp' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
        ]);

        $product = Category::create($validated);
        return response()->json(['success' => true, 'data' => $product], 201);
    }

    public function update(Request $request, $id)
    {
        $product = Category::find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $validated = $request->validate([
            'product_name' => 'sometimes|required|string|max:255',
            'mrp' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|nullable|integer|min:0',
        ]);

        $product->update($validated);
        return response()->json(['success' => true, 'data' => $product], 200);
    }

    public function destroy($id)
    {
        $product = Category::find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }
        $product->delete();
        return response()->json(['success' => true, 'message' => 'Product deleted successfully'], 200);
    }
}
