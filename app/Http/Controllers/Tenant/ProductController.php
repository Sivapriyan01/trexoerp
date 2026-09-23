<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category; // This is actually our Product model
use App\Models\StockLog;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Category::latest()->get();
        return view('tenant.products.index', compact('products'));
    }

    public function create()
    {
        return view('tenant.products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'barcode' => 'nullable|string|unique:categories,barcode',
            'brand' => 'nullable|string|max:255',
            'product_type' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'hsn' => 'nullable|string|max:255',
            'mrp' => 'required|numeric|min:0',
            'dealer_price' => 'nullable|numeric|min:0',
            'gst' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'low_stock_alert' => 'nullable|integer|min:0',
            'color' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
            'expiry_date' => 'nullable|date',
            'show_on_website' => 'nullable|boolean',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $validated['show_on_website'] = $request->boolean('show_on_website');

        // Auto-calculate CGST/SGST if not provided
        if (isset($validated['gst'])) {
            $validated['cgst'] = $validated['gst'] / 2;
            $validated['sgst'] = $validated['gst'] / 2;
        }

        Category::create($validated);

        return redirect()->route('tenant.products.index')->with('success', 'Product added to master successfully');
    }

    public function show($id)
    {
        $product = Category::findOrFail($id);
        return view('tenant.products.edit', compact('product'));
    }

    public function edit($id)
    {
        $product = Category::findOrFail($id);
        return view('tenant.products.edit', compact('product'));
    }

    public function update(Request $request, $id)
    {
        $product = Category::findOrFail($id);
        
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'barcode' => 'nullable|string|unique:categories,barcode,'.$product->id,
            'brand' => 'nullable|string|max:255',
            'product_type' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'hsn' => 'nullable|string|max:255',
            'mrp' => 'required|numeric|min:0',
            'dealer_price' => 'nullable|numeric|min:0',
            'gst' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'low_stock_alert' => 'nullable|integer|min:0',
            'color' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'expiry_date' => 'nullable|date',
            'show_on_website' => 'nullable|boolean',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $validated['show_on_website'] = $request->boolean('show_on_website');

        if (isset($validated['gst'])) {
            $validated['cgst'] = $validated['gst'] / 2;
            $validated['sgst'] = $validated['gst'] / 2;
        }

        $product->update($validated);

        return redirect()->route('tenant.products.index')->with('success', 'Product updated successfully');
    }

    public function toggleWebsite($id)
    {
        $product = Category::findOrFail($id);
        $product->show_on_website = !$product->show_on_website;
        $product->save();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'show_on_website' => (bool) $product->show_on_website,
                'message' => $product->show_on_website ? 'Product is now visible on website' : 'Product hidden from website',
            ]);
        }

        return back()->with('success', $product->show_on_website ? 'Product added to website!' : 'Product removed from website!');
    }

    public function destroy($id)
    {
        $product = Category::findOrFail($id);
        $product->delete();
        return redirect()->route('tenant.products.index')->with('success', 'Product deleted successfully');
    }

    public function inventory()
    {
        $products = Category::where('is_active', true)->latest()->get();
        return view('tenant.inventory.index', compact('products'));
    }

    public function history(Request $request)
    {
        $query = StockLog::with('product')->latest();

        if ($request->filled('product_id')) {
            $query->where('category_id', $request->product_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $logs = $query->paginate(50);
        $products = Category::where('is_active', true)->orderBy('product_name')->get();

        return view('tenant.inventory.history', compact('logs', 'products'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\ProductsImport, $request->file('file'));
            return redirect()->back()->with('success', 'Products imported successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing products: ' . $e->getMessage());
        }
    }
}
