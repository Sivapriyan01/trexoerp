<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReminderController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        // Reminders Today (Bills + Customers)
        $todayBillReminders = Bill::where('reminder_enabled', true)
            ->whereMonth('bill_date', $today->month)
            ->whereDay('bill_date', $today->day)
            ->whereYear('bill_date', '<', $today->year)
            ->get();
        
        $todayCustomerReminders = \App\Models\Customer::where('anniversary_reminder_enabled', true)
            ->whereMonth('anniversary_date', $today->month)
            ->whereDay('anniversary_date', $today->day)
            ->get();

        $todayReminders = $todayBillReminders->concat($todayCustomerReminders);

        // Upcoming (Next 7 days)
        $upcomingReminders = collect();
        for ($i = 1; $i <= 7; $i++) {
            $date = $today->copy()->addDays($i);
            
            // From Bills
            Bill::where('reminder_enabled', true)
                ->whereMonth('bill_date', $date->month)
                ->whereDay('bill_date', $date->day)
                ->whereYear('bill_date', '<', $today->year)
                ->get()
                ->each(function($b) use ($date, $today, $upcomingReminders) {
                    $b->upcoming_date = $date->format('d M');
                    $b->years = $today->year - $b->bill_date->year;
                    $b->type = 'Bill Anniversary';
                    $upcomingReminders->push($b);
                });

            // From Customers
            \App\Models\Customer::where('anniversary_reminder_enabled', true)
                ->whereMonth('anniversary_date', $date->month)
                ->whereDay('anniversary_date', $date->day)
                ->get()
                ->each(function($c) use ($date, $today, $upcomingReminders) {
                    $c->upcoming_date = $date->format('d M');
                    $c->years = $today->year - $c->anniversary_date->year;
                    $c->type = 'Customer Anniversary';
                    $upcomingReminders->push($c);
                });
        }

        // Stats
        $stats = [
            'today_count' => $todayReminders->count(),
            'upcoming_count' => $upcomingReminders->count(),
            'total_active' => Bill::where('reminder_enabled', true)->count() + \App\Models\Customer::where('anniversary_reminder_enabled', true)->whereNotNull('anniversary_date')->count(),
        ];

        return view('tenant.reminders.index', compact('todayReminders', 'upcomingReminders', 'stats'));
    }

    public function sendManual(Request $request)
    {
        $id = $request->id;
        $type = $request->type; // 'bill' or 'customer'

        $whatsappService = new \App\Services\WhatsappService();
        $messageTemplate = \App\Models\Setting::get('anniversary_reminder_message', "Dear {customer_name},\n\nHappy {years} Year Anniversary with {business_name}!\n\nWe appreciate your continued support.");
        $businessName = \App\Models\Setting::get('branch_name', 'Our Store');

        if ($type === 'bill') {
            $data = Bill::findOrFail($id);
            $name = $data->customer_name;
            $phone = $data->customer_phone;
            $years = now()->year - $data->bill_date->year;
            $date = $data->bill_date->format('d-m-Y');
            $invoiceNo = $data->invoice_no;
        } else {
            $data = \App\Models\Customer::findOrFail($id);
            $name = $data->name;
            $phone = $data->phone;
            $anniversaryDate = \Illuminate\Support\Carbon::parse($data->anniversary_date);
            $years = now()->year - $anniversaryDate->year;
            $date = $anniversaryDate->format('d-m-Y');
            $invoiceNo = 'Profile';
        }

        if (!$phone) {
            return response()->json(['success' => false, 'message' => 'Customer phone number not found.']);
        }

        $message = str_replace(
            ['{customer_name}', '{years}', '{date}', '{invoice_no}', '{business_name}'],
            [$name, $years, $date, $invoiceNo, $businessName],
            $messageTemplate
        );

        $result = $whatsappService->sendMessage($phone, $message);

        return response()->json($result);
    }
}
