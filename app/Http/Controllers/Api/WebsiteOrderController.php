<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Category;
use App\Models\Customer;
use App\Models\tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\Msg91Service;
use App\Services\WhatsappService;

class WebsiteOrderController extends Controller
{
    /**
     * Helper to run queries inside a specific tenant context
     */
    protected function runInTenant(Request $request, callable $callback)
    {
        $tenantId = $request->header('X-Tenant') 
            ?: $request->query('tenant') 
            ?: $request->input('tenant_id');

        // If not explicitly provided, try extracting from Origin / Referer / Host
        if (!$tenantId) {
            $origin = $request->header('Origin') ?: $request->header('Referer') ?: $request->getHost();
            if ($origin) {
                $host = parse_url($origin, PHP_URL_HOST) ?: $origin;
                $parts = explode('.', $host);
                if (count($parts) > 1 && !in_array($parts[0], ['localhost', '127', 'www'])) {
                    $tenantId = $parts[0];
                }
            }
        }

        // Fallback to active tenancy or primary store tenant
        if (!$tenantId && function_exists('tenant') && tenant('id')) {
            $tenantId = tenant('id');
        }
        if (!$tenantId) {
            $tenantId = 'avinash';
        }

        $tenant = tenant::find($tenantId) ?: tenant::whereNotNull('id')->first();

        if ($tenant) {
            tenancy()->initialize($tenant);
        }

        try {
            return $callback();
        } finally {
            if ($tenant) {
                tenancy()->end();
            }
        }
    }

    /**
     * Health check endpoint
     */
    public function health()
    {
        return response()->json([
            'status' => 'ok',
            'system' => 'Square ERP Order Management API',
            'version' => '2.0.0',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Fetch products from backend ERP (Tenant context)
     * Only returns products specifically enabled for the website
     */
    public function getProducts(Request $request)
    {
        return $this->runInTenant($request, function () use ($request) {
            try {
                // Only return active products that are specifically marked to show on the website
                $categories = Category::where(function ($q) {
                        $q->whereNull('is_active')->orWhere('is_active', true);
                    })
                    ->where('show_on_website', true)
                    ->latest()
                    ->get();

                $isGstEnabled = \App\Models\Setting::get('gst_enabled', \App\Models\Setting::get('bill_gst_enabled', '1')) == '1';
                $defaultGst = (float) \App\Models\Setting::get('gst_default_percent', \App\Models\Setting::get('billing_tax_percent', 0));
                $globalDiscount = (float) \App\Models\Setting::get('website_discount_percent', 0);
                $tenantId = function_exists('tenant') && tenant('id') ? tenant('id') : ($request->input('tenant') ?: 'avinash');

                $products = $categories->map(function ($cat) use ($isGstEnabled, $defaultGst, $globalDiscount, $tenantId) {
                    $images = [
                        'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=600&auto=format&fit=crop&q=80',
                        'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=600&auto=format&fit=crop&q=80',
                        'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=600&auto=format&fit=crop&q=80',
                        'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?w=600&auto=format&fit=crop&q=80',
                    ];

                    if ($cat->image) {
                        if (str_starts_with($cat->image, 'http://') || str_starts_with($cat->image, 'https://')) {
                            $img = $cat->image;
                        } else {
                            $img = '/api/v1/assets/' . ltrim($cat->image, '/') . ($tenantId ? '?tenant=' . urlencode($tenantId) : '');
                        }
                    } else {
                        $img = $images[$cat->id % count($images)];
                    }

                    $origMrp = (float) ($cat->mrp ?: 0);
                    $prodDiscount = ($cat->discount_percent !== null && (float)$cat->discount_percent > 0)
                        ? (float) $cat->discount_percent
                        : $globalDiscount;

                    if ($prodDiscount > 0) {
                        $sellingPrice = round($origMrp * (1 - ($prodDiscount / 100)), 2);
                        $displayMrp = $origMrp;
                        $displayDiscount = $prodDiscount;
                    } else {
                        $sellingPrice = $origMrp;
                        $displayMrp = $origMrp;
                        $displayDiscount = 0;
                    }

                    return [
                        'id' => $cat->id,
                        'sku' => $cat->barcode ?: ('SQ-CAT-' . $cat->id),
                        'barcode' => $cat->barcode,
                        'product_name' => $cat->product_name,
                        'category' => $cat->product_type ?: 'General',
                        'brand' => $cat->brand ?: 'Square Store',
                        'description' => $cat->model ? "Model: {$cat->model}. Size: {$cat->size}." : "Authentic quality directly from store.",
                        'selling_price' => $sellingPrice,
                        'mrp' => $displayMrp,
                        'discount' => $displayDiscount,
                        'tax' => $isGstEnabled ? (float) ($cat->gst !== null && $cat->gst !== '' ? $cat->gst : $defaultGst) : 0.0,
                        'stock' => (int) $cat->stock,
                        'minimum_stock' => (int) ($cat->low_stock_alert ?: 5),
                        'status' => $cat->stock > 0 ? 'ACTIVE' : 'OUT_OF_STOCK',
                        'image' => $img,
                    ];
                });

                return response()->json([
                    'success' => true,
                    'count' => $products->count(),
                    'products' => $products,
                ]);
            } catch (\Throwable $e) {
                Log::error('Error fetching website products: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to fetch products: ' . $e->getMessage(),
                ], 500);
            }
        });
    }

    /**
     * Serve tenant product images and assets
     */
    public function getAsset(Request $request, $path)
    {
        return $this->runInTenant($request, function () use ($path, $request) {
            $cleanPath = ltrim($path, '/');

            // 1. Check in active tenant public storage
            $realPath = storage_path('app/public/' . $cleanPath);
            if (file_exists($realPath) && is_file($realPath)) {
                return $this->serveAssetFile($realPath);
            }

            // 2. Check explicit tenant directory if known
            $tenantId = function_exists('tenant') && tenant('id') ? tenant('id') : ($request->input('tenant') ?: null);
            if ($tenantId) {
                $tenantStoragePath = base_path("storage/tenant{$tenantId}/app/public/{$cleanPath}");
                if (file_exists($tenantStoragePath) && is_file($tenantStoragePath)) {
                    return $this->serveAssetFile($tenantStoragePath);
                }
            }

            // 3. Fallback scan across all tenant storage directories
            $matches = glob(base_path("storage/tenant*/app/public/{$cleanPath}"));
            if (! empty($matches) && file_exists($matches[0]) && is_file($matches[0])) {
                return $this->serveAssetFile($matches[0]);
            }

            // 4. Check global public storage
            $globalPath = base_path('storage/app/public/' . $cleanPath);
            if (file_exists($globalPath) && is_file($globalPath)) {
                return $this->serveAssetFile($globalPath);
            }

            abort(404, 'Asset not found');
        });
    }

    protected function serveAssetFile(string $filePath)
    {
        $mime = mime_content_type($filePath) ?: 'application/octet-stream';
        return response()->file($filePath, [
            'Content-Type'  => $mime,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Create Order from Website
     * Implements Section 4 (Order Fetcher), Section 7 (Customer Deduplication), and Section 21 (Idempotency)
     */
    public function createOrder(Request $request)
    {
        if (!$request->has('customer') && $request->filled('customer_name')) {
            $request->merge([
                'customer' => [
                    'name' => $request->input('customer_name'),
                    'phone' => $request->input('customer_phone'),
                    'email' => $request->input('customer_email'),
                    'shipping_address' => $request->input('shipping_address'),
                    'billing_address' => $request->input('billing_address', $request->input('shipping_address')),
                ],
            ]);
        }
        if (!$request->has('website_order_id')) {
            $request->merge([
                'website_order_id' => 'WEB-' . date('YmdHis') . '-' . rand(1000, 9999),
            ]);
        }

        $validated = $request->validate([
            'website_order_id' => 'required|string',
            'customer.name' => 'required|string',
            'customer.phone' => 'required|string',
            'customer.email' => 'nullable|email',
            'customer.shipping_address' => 'required|string',
            'customer.billing_address' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable',
            'items.*.product_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric',
            'tax' => 'nullable|numeric',
            'shipping_charge' => 'nullable|numeric',
            'grand_total' => 'required|numeric',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        return $this->runInTenant($request, function () use ($validated) {
            return DB::transaction(function () use ($validated) {
                $custData = $validated['customer'];

                // 1. Idempotency Check: Prevent duplicate order processing
                $existingBill = Bill::where('remarks', 'like', '%' . $validated['website_order_id'] . '%')->first();
                if ($existingBill) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Order already processed (Idempotent response).',
                        'order' => [
                            'id' => $existingBill->id,
                            'website_order_id' => $validated['website_order_id'],
                            'invoice_no' => $existingBill->invoice_no,
                            'order_status' => $existingBill->status,
                            'grand_total' => (float) $existingBill->grand_total,
                        ],
                    ]);
                }

                // 2. Customer Deduplication (Section 7 of PDF)
                $customer = Customer::where('phone', $custData['phone'])->first();
                if (!$customer && !empty($custData['email'])) {
                    $customer = Customer::where('email', $custData['email'])->first();
                }

                if ($customer) {
                    $customer->update([
                        'name' => $customer->name ?: $custData['name'],
                        'address' => $custData['shipping_address'],
                        'bill_count' => ($customer->bill_count ?: 0) + 1,
                    ]);
                } else {
                    $customer = Customer::create([
                        'name' => $custData['name'],
                        'phone' => $custData['phone'],
                        'email' => $custData['email'] ?? null,
                        'address' => $custData['shipping_address'],
                        'bill_count' => 1,
                    ]);
                }

                // 3. Generate Invoice Number & Bill Record
                $billDate = now();
                $invoiceNo = 'INV-' . $billDate->format('Ymd') . '-' . str_pad(Bill::count() + 1, 4, '0', STR_PAD_LEFT);

                $bill = Bill::create([
                    'invoice_no' => $invoiceNo,
                    'bill_type' => 'website_order',
                    'customer_id' => $customer->id,
                    'customer_phone' => $customer->phone,
                    'customer_email' => $customer->email,
                    'customer_name' => $customer->name,
                    'customer_address' => $custData['shipping_address'],
                    'bill_date' => $billDate->toDateString(),
                    'subtotal' => $validated['subtotal'],
                    'gst_amount' => $validated['tax'] ?? 0,
                    'grand_total' => $validated['grand_total'],
                    'paid_amount' => $validated['payment_method'] === 'COD' ? 0 : $validated['grand_total'],
                    'balance' => $validated['payment_method'] === 'COD' ? $validated['grand_total'] : 0,
                    'payment_mode' => strtolower($validated['payment_method'] ?? 'online'),
                    'status' => 'CONFIRMED',
                    'remarks' => 'Website Order ID: ' . $validated['website_order_id'] . (!empty($validated['notes']) ? ' | ' . $validated['notes'] : ''),
                ]);

                // 4. Create Order Items & Reserve Inventory
                foreach ($validated['items'] as $item) {
                    BillItem::create([
                        'bill_id' => $bill->id,
                        'category_id' => $item['product_id'] ?? null,
                        'product_name' => $item['product_name'],
                        'mrp' => $item['price'],
                        'quantity' => $item['quantity'],
                        'total' => $item['price'] * $item['quantity'],
                    ]);

                    if (!empty($item['product_id'])) {
                        $prod = Category::find($item['product_id']);
                        if ($prod && $prod->stock > 0) {
                            $prod->decrement('stock', min($prod->stock, $item['quantity']));
                        }
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Order created and synchronized with Square ERP successfully.',
                    'order' => [
                        'id' => $bill->id,
                        'website_order_id' => $validated['website_order_id'],
                        'invoice_no' => $bill->invoice_no,
                        'order_status' => 'CONFIRMED',
                        'tracking_number' => 'TRK-SQ-' . rand(10000000, 99999999),
                        'courier' => 'BlueDart Express',
                        'grand_total' => (float) $bill->grand_total,
                        'customer' => [
                            'name' => $customer->name,
                            'phone' => $customer->phone,
                        ],
                        'history' => [
                            [
                                'status' => 'NEW',
                                'timestamp' => now()->toIso8601String(),
                                'note' => 'Order received via Website REST API',
                            ],
                            [
                                'status' => 'CONFIRMED',
                                'timestamp' => now()->toIso8601String(),
                                'note' => 'Customer verified and stock reserved in ERP',
                            ]
                        ]
                    ],
                ], 201);
            });
        });
    }

    /**
     * Get Order Status and Tracking
     */
    public function getOrder(Request $request, $id)
    {
        return $this->runInTenant($request, function () use ($id) {
            $id = trim((string) $id);
            $cleanDigits = preg_replace('/[^0-9]/', '', $id);

            $query = Bill::with(['customer', 'items']);

            $query->where(function ($q) use ($id, $cleanDigits) {
                $q->where('invoice_no', $id)
                  ->orWhere('remarks', 'like', '%' . $id . '%');

                // If short numeric, match bill ID
                if (is_numeric($id) && strlen($id) <= 8) {
                    $q->orWhere('id', (int) $id);
                }

                // Phone search
                if (!empty($cleanDigits) && strlen($cleanDigits) >= 7) {
                    $tenDigit = strlen($cleanDigits) >= 10 ? substr($cleanDigits, -10) : $cleanDigits;
                    $q->orWhere('customer_phone', $id)
                      ->orWhere('customer_phone', $cleanDigits)
                      ->orWhere('customer_phone', $tenDigit)
                      ->orWhere('customer_phone', 'like', '%' . $tenDigit . '%')
                      ->orWhereHas('customer', function ($cq) use ($cleanDigits, $tenDigit, $id) {
                          $cq->where('phone', $id)
                            ->orWhere('phone', $cleanDigits)
                            ->orWhere('phone', $tenDigit)
                            ->orWhere('phone', 'like', '%' . $tenDigit . '%');
                      });
                }
            });

            $bill = $query->latest('id')->first();

            if (!$bill) {
                return response()->json([
                    'success' => false,
                    'message' => 'No order found with this Order ID or Mobile number.',
                ], 404);
            }

            preg_match('/Website Order ID:\s*([A-Za-z0-9\-]+)/', $bill->remarks, $matches);
            $websiteOrderId = $matches[1] ?? ('WEB-' . $bill->id);

            return response()->json([
                'success' => true,
                'order' => [
                    'id' => $bill->id,
                    'website_order_id' => $websiteOrderId,
                    'invoice_no' => $bill->invoice_no,
                    'order_number' => $bill->invoice_no,
                    'status' => $bill->status ?: 'CONFIRMED',
                    'order_status' => $bill->status ?: 'CONFIRMED',
                    'tracking_number' => 'TRK-SQ-' . (10000000 + $bill->id),
                    'courier' => 'BlueDart Express',
                    'grand_total' => (float) $bill->grand_total,
                    'items' => $bill->items,
                    'customer' => [
                        'name' => $bill->customer_name,
                        'phone' => $bill->customer_phone,
                        'address' => $bill->customer_address,
                    ],
                    'history' => [
                        [
                            'status' => 'NEW',
                            'timestamp' => $bill->created_at->toIso8601String(),
                            'note' => 'Order placed on website',
                        ],
                        [
                            'status' => $bill->status ?: 'CONFIRMED',
                            'timestamp' => $bill->updated_at->toIso8601String(),
                            'note' => 'Status confirmed by Square ERP',
                        ],
                    ]
                ],
            ]);
        });
    }

    /**
     * Send OTP to customer phone (via MSG91 SMS, WhatsApp, or simulation fallback)
     */
    public function sendOtp(Request $request)
    {
        return $this->runInTenant($request, function () use ($request) {
            $phone = preg_replace('/[^0-9]/', '', $request->input('phone', ''));
            if (strlen($phone) === 11 && str_starts_with($phone, '0')) {
                $phone = substr($phone, 1);
            }
            if (strlen($phone) === 12 && str_starts_with($phone, '91')) {
                $phone = substr($phone, 2);
            }
            if (strlen($phone) < 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide a valid 10-digit mobile number.',
                ], 422);
            }

            // Generate 4-digit code
            $otp = (string) rand(1000, 9999);
            try {
                Cache::store('file')->put('web_otp_' . $phone, $otp, now()->addMinutes(10));
            } catch (\Throwable $e) {
                Cache::put('web_otp_' . $phone, $otp, now()->addMinutes(10));
            }

            $message = "Your verification OTP for Square Store is: {$otp}. Please do not share this code with anyone.";

            $channel = 'simulation';
            $sent = false;
            $statusMessage = "OTP generated successfully.";

            // 1. Try MSG91 SMS Gateway if configured
            try {
                /** @var Msg91Service $msg91 */
                $msg91 = app(Msg91Service::class);
                if ($msg91->isConfigured()) {
                    $res = $msg91->sendOtp($phone, $otp);
                    if (!empty($res['success'])) {
                        $channel = 'sms';
                        $sent = true;
                        $statusMessage = "OTP sent to your phone via SMS!";
                    } else {
                        Log::warning("MSG91 SMS OTP failed: " . ($res['message'] ?? 'Unknown MSG91 error'));
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("MSG91 Service Exception: " . $e->getMessage());
            }

            // 2. Fallback to WhatsApp API Service if SMS not sent
            if (!$sent) {
                try {
                    if (class_exists(WhatsappService::class)) {
                        $wa = app(WhatsappService::class);
                        $res = $wa->sendMessage($phone, $message);
                        if ($res && !empty($res['success'])) {
                            $channel = 'whatsapp';
                            $sent = true;
                            $statusMessage = "OTP sent to your phone via WhatsApp!";
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning("Could not dispatch WhatsApp OTP: " . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => $statusMessage,
                'channel' => $channel,
            ]);
        });
    }

    /**
     * Verify OTP code
     */
    public function verifyOtp(Request $request)
    {
        return $this->runInTenant($request, function () use ($request) {
            $phone = preg_replace('/[^0-9]/', '', $request->input('phone', ''));
            if (strlen($phone) === 11 && str_starts_with($phone, '0')) {
                $phone = substr($phone, 1);
            }
            if (strlen($phone) === 12 && str_starts_with($phone, '91')) {
                $phone = substr($phone, 2);
            }
            $enteredOtp = trim($request->input('otp', ''));

            $cachedOtp = null;
            try {
                $cachedOtp = Cache::store('file')->get('web_otp_' . $phone);
            } catch (\Throwable $e) {
                try {
                    $cachedOtp = Cache::get('web_otp_' . $phone);
                } catch (\Throwable $e2) {
                    $cachedOtp = null;
                }
            }

            $isMatch = false;

            // 1. Check if verified by client-side MSG91 widget, master OTP 1234, or cached OTP
            if ($request->boolean('verified_by_widget') || $enteredOtp === '1234' || ($cachedOtp && $cachedOtp === $enteredOtp)) {
                $isMatch = true;
            } else {
                // 2. Fallback check with MSG91 verify API in case MSG91 generated the code
                try {
                    /** @var Msg91Service $msg91 */
                    $msg91 = app(Msg91Service::class);
                    if ($msg91->isConfigured()) {
                        $verifyRes = $msg91->verifyOtp($phone, $enteredOtp);
                        if (!empty($verifyRes['success'])) {
                            $isMatch = true;
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning("MSG91 verify exception: " . $e->getMessage());
                }
            }

            if ($isMatch) {
                try {
                    Cache::store('file')->forget('web_otp_' . $phone);
                } catch (\Throwable $e) {
                    Cache::forget('web_otp_' . $phone);
                }

                // Check if customer exists in ERP database to pre-populate name & address
                $customer = Customer::where('phone', 'like', "%{$phone}%")->first();

                return response()->json([
                    'success' => true,
                    'message' => 'Mobile number verified successfully!',
                    'customer' => $customer ? [
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'address' => $customer->address,
                    ] : null,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired verification code.',
            ], 422);
        });
    }

    /**
     * Cancel Website Order
     */
    public function cancelOrder(Request $request, $id)
    {
        return $this->runInTenant($request, function () use ($request, $id) {
            $reason = $request->input('reason', 'Customer requested cancellation');

            $bill = Bill::where('invoice_no', $id)
                ->orWhere('remarks', 'like', '%' . $id . '%');
            if (is_numeric($id)) $bill->orWhere('id', (int) $id);
            $order = $bill->first();

            if (!$order) {
                return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
            }

            if (in_array($order->status, ['SHIPPED', 'DELIVERED'])) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Cannot cancel an order that has already been shipped or delivered. Please submit a Return Request instead.'
                ], 422);
            }

            $order->update([
                'status' => 'CANCELLED',
                'remarks' => $order->remarks . " | Cancelled: {$reason}",
            ]);

            // Restore product inventory
            foreach ($order->items as $item) {
                if ($item->category_id) {
                    $prod = Category::find($item->category_id);
                    if ($prod) $prod->increment('stock', $item->quantity);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Order #{$order->invoice_no} has been cancelled successfully.",
                'order' => [
                    'id' => $order->id,
                    'status' => 'CANCELLED',
                ]
            ]);
        });
    }

    /**
     * Return Website Order Request
     */
    public function returnOrder(Request $request, $id)
    {
        return $this->runInTenant($request, function () use ($request, $id) {
            $reason = $request->input('reason', 'Product return requested by customer');

            $bill = Bill::where('invoice_no', $id)
                ->orWhere('remarks', 'like', '%' . $id . '%');
            if (is_numeric($id)) $bill->orWhere('id', (int) $id);
            $order = $bill->first();

            if (!$order) {
                return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
            }

            $order->update([
                'status' => 'RETURN_REQUESTED',
                'remarks' => $order->remarks . " | Return Requested: {$reason}",
            ]);

            return response()->json([
                'success' => true,
                'message' => "Return request registered for Order #{$order->invoice_no}. Support will review within 24 hours.",
                'order' => [
                    'id' => $order->id,
                    'status' => 'RETURN_REQUESTED',
                ]
            ]);
        });
    }

    /**
     * Get Storefront & OTP Settings for Website
     */
    public function getSettings(Request $request)
    {
        return $this->runInTenant($request, function () use ($request) {
            $tenantId = function_exists('tenant') && tenant('id') ? tenant('id') : ($request->input('tenant') ?: 'avinash');

            $rawLogo = \App\Models\Setting::get('website_logo', \App\Models\Setting::get('business_logo', ''));
            $logo = '';
            if ($rawLogo) {
                if (str_starts_with($rawLogo, 'http://') || str_starts_with($rawLogo, 'https://')) {
                    $logo = $rawLogo;
                } else {
                    $logo = '/api/v1/assets/' . ltrim($rawLogo, '/') . ($tenantId ? '?tenant=' . urlencode($tenantId) : '');
                }
            }

            $rawHero = \App\Models\Setting::get('website_hero_image', '');
            $heroImage = '';
            if ($rawHero) {
                if (str_starts_with($rawHero, 'http://') || str_starts_with($rawHero, 'https://')) {
                    $heroImage = $rawHero;
                } else {
                    $heroImage = '/api/v1/assets/' . ltrim($rawHero, '/') . ($tenantId ? '?tenant=' . urlencode($tenantId) : '');
                }
            }

            $settings = [
                    'widget_id'          => \App\Models\Setting::get('msg91_widget_id', env('MSG91_TEMPLATE_ID', '36697164476b323432353839')),
                    'token_auth'         => \App\Models\Setting::get('msg91_token_auth', env('MSG91_AUTH_KEY', '572040TDOpHdLN6aab6e64P1')),
                    'otp_required'       => \App\Models\Setting::get('website_otp_required', '1') === '1',
                    'store_name'         => \App\Models\Setting::get('website_store_name', 'Square Store'),
                    'logo'               => $logo,
                    'hero_image'         => $heroImage,
                    'support_phone'      => \App\Models\Setting::get('website_support_phone', '6383272563'),
                    'support_email'      => \App\Models\Setting::get('website_support_email', 'support@squarestore.in'),
                    'free_delivery_min'  => (float) \App\Models\Setting::get('website_free_delivery_min', 999),
                    'shipping_fee'       => (float) \App\Models\Setting::get('website_shipping_fee', 99),
                    'announcement'       => \App\Models\Setting::get('website_announcement', '🎉 FREE Delivery on orders above ₹999! Verified Authentic Stock.'),
                    'gst_calc_type'      => \App\Models\Setting::get('gst_calc_type', 'inclusive'),
                    'gst_enabled'        => \App\Models\Setting::get('gst_enabled', \App\Models\Setting::get('bill_gst_enabled', '1')) == '1',
                    'default_gst'        => (float) \App\Models\Setting::get('gst_default_percent', \App\Models\Setting::get('billing_tax_percent', 0)),
                    'template'           => \App\Models\Setting::get('website_template', 'modern_minimal'),
                    'primary_color'      => \App\Models\Setting::get('website_primary_color', '#10b981'),
                    'accent_color'       => \App\Models\Setting::get('website_accent_color', '#059669'),
                    'text_color'         => \App\Models\Setting::get('website_text_color', '#0f172a'),
                    'font_family'        => \App\Models\Setting::get('website_font_family', 'Inter'),
                    'hero_title'         => \App\Models\Setting::get('website_hero_title', 'Seamless Shopping. Instant ERP Fulfillment.'),
                    'hero_subtitle'      => \App\Models\Setting::get('website_hero_subtitle', 'Browse authentic products with live warehouse stock sync, instant GST-compliant invoicing, and automated courier dispatch.'),
                    'hero_badge'         => \App\Models\Setting::get('website_hero_badge', 'Official ERP Connected Store'),
                    'hero_cta_text'      => \App\Models\Setting::get('website_hero_cta_text', 'Explore Catalog'),
                    'products_title'     => \App\Models\Setting::get('website_products_title', 'All Products'),
                    'products_subtitle'  => \App\Models\Setting::get('website_products_subtitle', ''),
                    'card_style'         => \App\Models\Setting::get('website_card_style', 'modern'),
                    'business_type'      => \App\Models\Setting::get('website_business_type', 'General Store'),
                    'positioning'        => \App\Models\Setting::get('website_positioning', ''),
                    'palette_name'       => \App\Models\Setting::get('website_palette_name', 'Emerald'),
                    'layout_style'       => \App\Models\Setting::get('website_layout_style', 'split'),
                    'border_radius'      => \App\Models\Setting::get('website_border_radius', '16px'),
                    'header_style'       => \App\Models\Setting::get('website_header_style', 'regular'),
                    'shadow_style'       => \App\Models\Setting::get('website_shadow_style', 'small'),
                    'secondary_color'    => \App\Models\Setting::get('website_secondary_color', '#f1f5f9'),
                    'bg_color'           => \App\Models\Setting::get('website_bg_color', '#ffffff'),
                    'footer_text'        => \App\Models\Setting::get('website_footer_text', ''),
                    'social_instagram'   => \App\Models\Setting::get('website_social_instagram', ''),
                    'social_facebook'    => \App\Models\Setting::get('website_social_facebook', ''),
                    'social_whatsapp'    => \App\Models\Setting::get('website_social_whatsapp', ''),
                    'seo_title'          => \App\Models\Setting::get('website_seo_title', ''),
                    'seo_description'    => \App\Models\Setting::get('website_seo_description', ''),
                    'sections_order'     => \App\Models\Setting::get('website_sections_order', json_encode(['announcement', 'header', 'hero', 'categories', 'products', 'features', 'footer'])),
                    'sections_visibility' => \App\Models\Setting::get('website_sections_visibility', json_encode(['announcement' => true, 'header' => true, 'hero' => true, 'categories' => true, 'products' => true, 'features' => true, 'footer' => true])),
                ];

                try {
                    $allWebsite = \App\Models\Setting::where('key', 'like', 'website_%')->get();
                    foreach ($allWebsite as $row) {
                        $cleanKey = substr($row->key, 8);
                        if (!isset($settings[$cleanKey])) {
                            $settings[$cleanKey] = $row->value;
                        }
                    }
                } catch (\Throwable $e) {}

                return response()->json([
                    'success' => true,
                    'settings' => $settings,
                ]);
            });
    }

    /**
     * Update website settings via API (used by live configurator preview)
     * PUT /api/v1/settings
     */
    public function updateSettings(Request $request)
    {
        return $this->runInTenant($request, function () use ($request) {
            foreach ($request->all() as $key => $value) {
                if (str_starts_with($key, 'website_') && !is_array($value)) {
                    \App\Models\Setting::set($key, (string) ($value ?? ''), 'website');
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Website settings updated successfully.',
                'updated' => array_keys($data),
            ]);
        });
    }

    /**
     * Get available template metadata (GET /api/v1/templates)
     */
    public function getTemplates()
    {
        $gallery = [
            ['id' => 'modern_minimal',    'name' => 'Modern Minimal',       'status' => 'active',       'preview_color' => '#f8fafc', 'tags' => ['minimal','editorial','white']],
            ['id' => 'premium_dark',      'name' => 'Premium Dark',          'status' => 'active',       'preview_color' => '#0f172a', 'tags' => ['dark','luxury','gold']],
            ['id' => 'bold_commerce',     'name' => 'Bold Commerce',         'status' => 'active',       'preview_color' => '#6366f1', 'tags' => ['bold','colorful','retail']],
            ['id' => 'clean_business',    'name' => 'Clean Business',        'status' => 'active',       'preview_color' => '#0ea5e9', 'tags' => ['corporate','professional']],
            ['id' => 'creative_commerce', 'name' => 'Creative Commerce',     'status' => 'active',       'preview_color' => '#ec4899', 'tags' => ['creative','art']],
            ['id' => 'luxury_store',      'name' => 'Luxury Store',          'status' => 'active',       'preview_color' => '#92400e', 'tags' => ['luxury']],
            ['id' => 'electronics_store', 'name' => 'Electronics Store',     'status' => 'active',       'preview_color' => '#1e40af', 'tags' => ['electronics','tech']],
            ['id' => 'fashion_store',     'name' => 'Fashion Store',         'status' => 'active',       'preview_color' => '#be185d', 'tags' => ['fashion']],
            ['id' => 'manufacturing',     'name' => 'Manufacturing Products', 'status' => 'active',      'preview_color' => '#78716c', 'tags' => ['manufacturing','b2b']],
            ['id' => 'industrial',        'name' => 'Industrial Business',   'status' => 'active',       'preview_color' => '#374151', 'tags' => ['industrial','b2b']],
            ['id' => 'corporate',         'name' => 'Corporate Website',     'status' => 'active',       'preview_color' => '#1d4ed8', 'tags' => ['corporate']],
            ['id' => 'bold_colorful',     'name' => 'Bold Colorful',         'status' => 'active',       'preview_color' => '#7c3aed', 'tags' => ['colorful']],
            ['id' => 'glassmorphism',     'name' => 'Glassmorphism',         'status' => 'active',       'preview_color' => '#3b82f6', 'tags' => ['glass','modern']],
            ['id' => 'classic_commerce',  'name' => 'Classic Commerce',      'status' => 'active',       'preview_color' => '#16a34a', 'tags' => ['classic']],
            ['id' => 'modern_grid',       'name' => 'Modern Grid',           'status' => 'active',       'preview_color' => '#d97706', 'tags' => ['grid','masonry']],
            ['id' => 'product_focused',   'name' => 'Product-Focused',       'status' => 'active',       'preview_color' => '#0891b2', 'tags' => ['product']],
            ['id' => 'pro_catalog',       'name' => 'Professional Catalog',  'status' => 'active',       'preview_color' => '#4f46e5', 'tags' => ['catalog']],
            ['id' => 'elegant_white',     'name' => 'Elegant White',         'status' => 'active',       'preview_color' => '#fafafa', 'tags' => ['elegant','serif']],
            ['id' => 'dark_industrial',   'name' => 'Dark Industrial',       'status' => 'active',       'preview_color' => '#1c1917', 'tags' => ['dark','industrial']],
            ['id' => 'modern_landing',    'name' => 'Modern Landing Page',   'status' => 'active',       'preview_color' => '#059669', 'tags' => ['landing']],
        ];

        $activeTemplate = \App\Models\Setting::get('website_template', 'modern_minimal');

        return response()->json([
            'success'         => true,
            'active_template' => $activeTemplate,
            'templates'       => $gallery,
        ]);
    }
}

