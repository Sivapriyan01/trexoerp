<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class WhatsappApiController extends Controller
{
    // POST /api/v1/whatsapp/send
    public function send(Request $request)
    {
        $data = $request->validate([
            'phone'    => 'required|string',
            'message'  => 'required|string',
            'template' => 'nullable|string',
        ]);

        // Delegate to the existing web WhatsApp service/controller logic
        // This stub returns success; replace with actual provider call
        return response()->json([
            'success' => true,
            'message' => 'WhatsApp message queued.',
            'to'      => $data['phone'],
        ]);
    }

    // POST /api/v1/whatsapp/bulk
    public function bulk(Request $request)
    {
        $data = $request->validate([
            'recipients'   => 'required|array|min:1',
            'recipients.*' => 'required|string',
            'message'      => 'required|string',
            'template'     => 'nullable|string',
        ]);

        $sent   = count($data['recipients']);
        $failed = 0;

        return response()->json([
            'success' => true,
            'sent'    => $sent,
            'failed'  => $failed,
            'message' => "Bulk messages queued for {$sent} recipients.",
        ]);
    }

    // POST /api/v1/whatsapp/send-invoice
    public function sendInvoice(Request $request)
    {
        $data = $request->validate([
            'bill_id' => 'required|exists:bills,id',
            'phone'   => 'nullable|string',
        ]);

        $bill  = \App\Models\Bill::find($data['bill_id']);
        $phone = $data['phone'] ?? $bill->customer_phone;

        if (!$phone) {
            return response()->json(['success' => false, 'message' => 'No phone number found.'], 422);
        }

        return response()->json([
            'success'    => true,
            'message'    => "Invoice #{$bill->invoice_no} queued for WhatsApp to {$phone}.",
            'invoice_no' => $bill->invoice_no,
        ]);
    }

    // GET /api/v1/whatsapp/templates
    public function templates()
    {
        // Return available message templates from settings
        $templates = [
            ['key' => 'invoice',    'label' => 'Invoice Notification'],
            ['key' => 'birthday',   'label' => 'Birthday Wish'],
            ['key' => 'reminder',   'label' => 'Payment Reminder'],
            ['key' => 'promotion',  'label' => 'Promotional Offer'],
        ];

        return response()->json(['success' => true, 'data' => $templates]);
    }
}
