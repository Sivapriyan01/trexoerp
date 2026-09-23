<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Customer;
use App\Services\WhatsappService;
use Illuminate\Http\Request;

class WhatsappController extends Controller
{
    protected $whatsapp;

    public function __construct(WhatsappService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    public function index()
    {
        $customers = Customer::whereNotNull('phone')->latest()->paginate(20);
        $recentBills = Bill::latest()->limit(10)->get();
        return view('tenant.whatsapp.index', compact('customers', 'recentBills'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string'
        ]);

        $result = $this->whatsapp->sendMessage($request->phone, $request->message);

        return response()->json($result);
    }

    public function sendBulk(Request $request)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        // Disable time limit for bulk operations
        set_time_limit(0);

        $customers = Customer::whereNotNull('phone')->get();
        $results = [
            'total' => $customers->count(),
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($customers as $customer) {
            try {
                $res = $this->whatsapp->sendMessage($customer->phone, $request->message);
                if ($res['success']) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = "Failed for {$customer->name}: " . ($res['message'] ?? 'Unknown error');
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Error for {$customer->name}: " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Broadcast finished! Success: {$results['success']}, Failed: {$results['failed']}",
            'results' => $results
        ]);
    }
}
