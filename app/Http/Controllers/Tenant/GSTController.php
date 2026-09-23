<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Purchase;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class GSTController extends Controller
{
    /**
     * GSTR-1: Outward Supplies Report (Sales)
     */
    public function gstr1(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year  = $request->input('year',  now()->year);

        $period = Carbon::createFromDate($year, $month, 1);
        $from   = $period->copy()->startOfMonth();
        $to     = $period->copy()->endOfMonth();

        // All taxable sales in the period (excluding zero-GST)
        $bills = Bill::with('items')
            ->whereBetween('bill_date', [$from, $to])
            ->where('bill_type', '!=', 'purchase') // outward only
            ->latest('bill_date')
            ->get();

        // Aggregate by GST slab
        $slabs = [];
        $totalTaxable  = 0;
        $totalGst      = 0;
        $totalGrandTotal = 0;

        foreach ($bills as $bill) {
            $taxable    = $bill->subtotal - $bill->discount_amount;
            $gst        = $bill->gst_amount;
            $rate       = $bill->gst_percent;
            $slabKey    = (string) $rate;

            if (!isset($slabs[$slabKey])) {
                $slabs[$slabKey] = [
                    'rate'      => $rate,
                    'taxable'   => 0,
                    'cgst'      => 0,
                    'sgst'      => 0,
                    'total_gst' => 0,
                    'invoices'  => 0,
                ];
            }

            $slabs[$slabKey]['taxable']   += $taxable;
            $slabs[$slabKey]['cgst']      += $gst / 2;
            $slabs[$slabKey]['sgst']      += $gst / 2;
            $slabs[$slabKey]['total_gst'] += $gst;
            $slabs[$slabKey]['invoices']++;

            $totalTaxable    += $taxable;
            $totalGst        += $gst;
            $totalGrandTotal += $bill->grand_total;
        }

        ksort($slabs);

        $settings = [
            'business_name'    => Setting::get('business_name', tenant('name')),
            'bill_gst_no'      => Setting::get('bill_gst_no', ''),
            'branch_address'   => Setting::get('branch_address', ''),
        ];

        $months = collect(range(1, 12))->mapWithKeys(fn($m) => [$m => Carbon::createFromDate(2000, $m, 1)->format('F')]);
        $years  = range(now()->year - 2, now()->year);

        return view('tenant.gst.gstr1', compact(
            'bills', 'slabs', 'totalTaxable', 'totalGst', 'totalGrandTotal',
            'month', 'year', 'from', 'to', 'settings', 'months', 'years'
        ));
    }

    /**
     * GSTR-3B: Monthly Summary Return
     */
    public function gstr3b(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year  = $request->input('year',  now()->year);

        $period = Carbon::createFromDate($year, $month, 1);
        $from   = $period->copy()->startOfMonth();
        $to     = $period->copy()->endOfMonth();

        // 3.1 — Outward Supplies
        $bills = Bill::whereBetween('bill_date', [$from, $to])->get();

        $outwardTaxable  = $bills->sum(fn($b) => $b->subtotal - $b->discount_amount);
        $outwardGst      = $bills->sum('gst_amount');
        $outwardCgst     = round($outwardGst / 2, 2);
        $outwardSgst     = round($outwardGst / 2, 2);
        $outwardGrandTotal = $bills->sum('grand_total');

        // 4 — ITC (Input Tax Credit) from Purchases
        $purchases = Purchase::whereBetween('invoice_date', [$from, $to])->get();

        $itcTaxable  = $purchases->sum(fn($p) => $p->total_amount - $p->gst_amount);
        $itcGst      = $purchases->sum('gst_amount');
        $itcCgst     = round($itcGst / 2, 2);
        $itcSgst     = round($itcGst / 2, 2);

        // 5 — Net Tax Payable
        $netCgst = max(0, $outwardCgst - $itcCgst);
        $netSgst = max(0, $outwardSgst - $itcSgst);
        $netTax  = $netCgst + $netSgst;

        $settings = [
            'business_name'  => Setting::get('business_name', tenant('name')),
            'bill_gst_no'    => Setting::get('bill_gst_no', ''),
            'branch_address' => Setting::get('branch_address', ''),
        ];

        $months = collect(range(1, 12))->mapWithKeys(fn($m) => [$m => Carbon::createFromDate(2000, $m, 1)->format('F')]);
        $years  = range(now()->year - 2, now()->year);

        return view('tenant.gst.gstr3b', compact(
            'outwardTaxable', 'outwardGst', 'outwardCgst', 'outwardSgst', 'outwardGrandTotal',
            'itcTaxable', 'itcGst', 'itcCgst', 'itcSgst',
            'netCgst', 'netSgst', 'netTax',
            'bills', 'purchases',
            'month', 'year', 'from', 'to', 'settings', 'months', 'years'
        ));
    }

    /**
     * Download GST Report as JSON
     */
    public function downloadJson(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year  = $request->input('year',  now()->year);
        $type  = $request->input('type', 'gstr1');

        $period = Carbon::createFromDate($year, $month, 1);
        $from   = $period->copy()->startOfMonth();
        $to     = $period->copy()->endOfMonth();

        if ($type === 'gstr1') {
            $bills = Bill::with('items')
                ->whereBetween('bill_date', [$from, $to])
                ->where('bill_type', '!=', 'purchase')
                ->get();

            // Prepare for JSON
            $data = $bills->map(function($bill) {
                return [
                    'invoice_no'    => $bill->invoice_no,
                    'date'          => $bill->bill_date->format('Y-m-d'),
                    'customer'      => $bill->customer_name,
                    'taxable_value' => round($bill->subtotal - $bill->discount_amount, 2),
                    'gst_percent'   => $bill->gst_percent,
                    'gst_amount'    => $bill->gst_amount,
                    'cgst'          => round($bill->gst_amount / 2, 2),
                    'sgst'          => round($bill->gst_amount / 2, 2),
                    'grand_total'   => $bill->grand_total,
                ];
            });
        } else {
            // GSTR-3B Summary
            $bills = Bill::whereBetween('bill_date', [$from, $to])->get();
            $purchases = Purchase::whereBetween('invoice_date', [$from, $to])->get();

            $data = [
                'period' => $period->format('F Y'),
                'outward_supplies' => [
                    'taxable_value' => $bills->sum(fn($b) => $b->subtotal - $b->discount_amount),
                    'total_gst'     => $bills->sum('gst_amount'),
                    'invoice_count' => $bills->count(),
                ],
                'itc_available' => [
                    'taxable_value' => $purchases->sum(fn($p) => $p->total_amount - $p->gst_amount),
                    'total_gst'     => $purchases->sum('gst_amount'),
                    'purchase_count' => $purchases->count(),
                ]
            ];
        }

        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="GST_'.$type.'_'.$year.'_'.$month.'.json"',
        ]);
    }

    /**
     * Validate GSTIN using RapidAPI (GST Insights API)
     */
    public function validateGstin(Request $request)
    {
        $gstin = strtoupper($request->input('gstin'));
        $apiKeyString = Setting::get('gst_api_key', env('RAPIDAPI_KEY'));
        
        $apiKeys = array_filter(array_map('trim', explode(',', $apiKeyString)));

        if (empty($apiKeys)) {
            return response()->json(['success' => false, 'message' => 'API Key not configured in Setup > GST API Configuration'], 500);
        }

        if (empty($gstin)) {
            return response()->json(['success' => false, 'message' => 'GSTIN is required'], 400);
        }

        $pattern = "/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}[A-Z0-9]{1}[0-9A-Z]{1}$/i";
        if (!preg_match($pattern, $gstin)) {
            return response()->json(['success' => false, 'message' => 'Invalid GSTIN format'], 422);
        }

        // Check cache first — instant response for repeated lookups
        $cacheKey = 'gst_' . $gstin;
        $cached = cache()->store('file')->get($cacheKey);
        if ($cached) {
            // Refresh target_months from settings in case super admin changed it
            $cached['data']['target_months'] = (int)env('GST_RETURN_TARGET_MONTHS', 8);
            return response()->json($cached);
        }

        shuffle($apiKeys);
        $lastError = 'Unknown error';
        $lastStatus = 500;

        $currentMonth = (int)date('m');
        $currentYear  = (int)date('Y');
        $finYear = ($currentMonth >= 4)
            ? ($currentYear . '-' . substr($currentYear + 1, -2))
            : (($currentYear - 1) . '-' . substr($currentYear, -2));

        $find = function($arr, $key) use (&$find) {
            if (!is_array($arr)) return null;
            $key = strtolower($key);
            foreach ($arr as $k => $v) {
                if (strtolower($k) === $key) return $v;
            }
            foreach ($arr as $v) {
                if (is_array($v)) {
                    $res = $find($v, $key);
                    if ($res !== null) return $res;
                }
            }
            return null;
        };

        foreach ($apiKeys as $apiKey) {
            try {
                // Phase 1: Find a working key fast — insights API only, short timeout
                $response = Http::withoutVerifying()
                    ->withHeaders([
                        'x-rapidapi-host' => 'gst-insights-api.p.rapidapi.com',
                        'x-rapidapi-key'  => $apiKey,
                    ])
                    ->timeout(5)
                    ->get("https://gst-insights-api.p.rapidapi.com/getGSTDetailsUsingGST/{$gstin}");

                // Dead key (quota/auth) — skip instantly, no waiting
                if (in_array($response->status(), [401, 403, 429])) {
                    $lastError  = 'API key quota exceeded or unauthorized';
                    $lastStatus = $response->status();
                    continue;
                }

                if (!$response->successful()) {
                    $errorData  = $response->json() ?? [];
                    $lastError  = $errorData['message'] ?? $errorData['error'] ?? 'RapidAPI Error (' . $response->status() . ')';
                    $lastStatus = $response->status();
                    return response()->json(['success' => false, 'message' => $lastError], $lastStatus);
                }

                $rawResponse = $response->json();

                if (isset($rawResponse['error']) && ($rawResponse['error'] === true || $rawResponse['error'] === 'true')) {
                    return response()->json([
                        'success' => false,
                        'message' => $rawResponse['message'] ?? 'GSTIN not found or invalid'
                    ], 404);
                }

                $data = $rawResponse['data'] ?? $rawResponse;

                $building = $find($data, 'bnm') ?? $find($data, 'bno') ?? $find($data, 'building_name') ?? '';
                $street   = $find($data, 'st')  ?? $find($data, 'street')   ?? '';
                $locality = $find($data, 'loc') ?? $find($data, 'locality') ?? '';
                $district = $find($data, 'dst') ?? $find($data, 'district') ?? '';
                $city     = $find($data, 'city') ?? $district ?: $locality;
                $state    = $find($data, 'stcd') ?? $find($data, 'st') ?? $find($data, 'state') ?? '';
                $pincode  = $find($data, 'pncd') ?? $find($data, 'pincode') ?? $find($data, 'pin') ?? '';

                $allAddrParts = array_filter([$building, $street, $locality, $district, $city, $state, $pincode]);

                if (count($allAddrParts) <= 1) {
                    $pradr   = $find($data, 'pradr') ?? $find($data, 'adadr') ?? [];
                    $addrObj = is_array($pradr) ? ($pradr['addr'] ?? (isset($pradr[0]) ? ($pradr[0]['addr'] ?? []) : [])) : [];
                    if (!empty($addrObj) && is_array($addrObj)) {
                        $allAddrParts = array_values(array_filter($addrObj, function($v) { return !is_array($v) && !empty($v); }));
                    }
                }

                $fullAddress = trim(implode(', ', $allAddrParts));
                $legalName   = $find($data, 'lgnm') ?? $find($data, 'tradeName') ?? $find($data, 'trade_name') ?? $find($data, 'legal_name');

                $result = [
                    'success' => true,
                    'message' => 'GSTIN Verified Successfully',
                    'data'    => [
                        'legal_name'    => $legalName ?? 'Unknown Business',
                        'address'       => $fullAddress ?: 'Address not available',
                        'city'          => is_string($city)    ? $city    : '',
                        'state'         => is_string($state)   ? $state   : '',
                        'pincode'       => is_string($pincode) ? $pincode : '',
                        'gstin'         => $gstin,
                        'status'        => $find($data, 'sts') ?? 'Active',
                        'target_months' => (int)env('GST_RETURN_TARGET_MONTHS', 8),
                    ]
                ];

                // Cache GST profile for 6 hours
                cache()->store('file')->put($cacheKey, $result, now()->addHours(6));

                return response()->json($result);

            } catch (\Exception $e) {
                $lastError  = 'Connection Error: ' . $e->getMessage();
                $lastStatus = 500;
                continue;
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'All API keys exhausted or invalid. Last error: ' . $lastError
        ], $lastStatus);
    }

    /**
     * Fetch GST Return Filing data separately (called async from frontend after verify)
     */
    public function fetchReturns(Request $request)
    {
        $gstin = strtoupper($request->input('gstin'));
        $apiKeyString = Setting::get('gst_api_key', env('RAPIDAPI_KEY'));
        $apiKeys = array_filter(array_map('trim', explode(',', $apiKeyString)));

        if (empty($apiKeys) || empty($gstin)) {
            return response()->json(['success' => false, 'returns' => null]);
        }

        // Check returns cache
        $cacheKey = 'gst_returns_' . $gstin;
        $cached = cache()->store('file')->get($cacheKey);
        if ($cached !== null) {
            return response()->json([
                'success'       => true,
                'returns'       => $cached,
                'target_months' => (int)env('GST_RETURN_TARGET_MONTHS', 8),
            ]);
        }

        $currentMonth = (int)date('m');
        $currentYear  = (int)date('Y');
        $finYear = ($currentMonth >= 4)
            ? ($currentYear . '-' . substr($currentYear + 1, -2))
            : (($currentYear - 1) . '-' . substr($currentYear, -2));

        shuffle($apiKeys);
        foreach ($apiKeys as $apiKey) {
            try {
                $response = Http::withoutVerifying()
                    ->withHeaders([
                        'x-rapidapi-host' => 'gst-verification-api-get-profile-returns-data.p.rapidapi.com',
                        'x-rapidapi-key'  => $apiKey,
                    ])
                    ->timeout(8)
                    ->get("https://gst-verification-api-get-profile-returns-data.p.rapidapi.com/v1/gstin/{$gstin}/return/{$finYear}");

                if (in_array($response->status(), [401, 403, 429])) continue;

                if ($response->successful()) {
                    $data = $response->json();
                    cache()->store('file')->put($cacheKey, $data, now()->addHours(6));
                    return response()->json([
                        'success'       => true,
                        'returns'       => $data,
                        'target_months' => (int)env('GST_RETURN_TARGET_MONTHS', 8),
                    ]);
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return response()->json(['success' => false, 'returns' => null]);
    }
}

