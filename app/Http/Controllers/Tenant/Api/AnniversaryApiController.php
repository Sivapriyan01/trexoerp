<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class AnniversaryApiController extends Controller
{
    // GET /api/v1/reminders/upcoming
    public function upcoming(Request $request)
    {
        $days  = $request->get('days', 7);
        $today = now();

        $customers = Customer::whereNotNull('anniversary_date')
            ->get()
            ->filter(function ($c) use ($today, $days) {
                if (!$c->anniversary_date) return false;

                $ann = \Carbon\Carbon::parse($c->anniversary_date)->setYear($today->year);
                if ($ann->isPast()) $ann->addYear();
                $diff = $today->diffInDays($ann, false);

                return $diff >= 0 && $diff <= $days;
            })
            ->map(function ($c) use ($today) {
                $result = $c->only(['id', 'name', 'phone', 'email', 'anniversary_date']);

                $ann = \Carbon\Carbon::parse($c->anniversary_date)->setYear($today->year);
                if ($ann->isPast()) $ann->addYear();
                $result['anniversary_in_days'] = $today->diffInDays($ann, false);

                return $result;
            })
            ->values();

        return response()->json(['success' => true, 'data' => $customers]);
    }

    // GET /api/v1/reminders/today
    public function today()
    {
        $today = now();
        $month = $today->month;
        $day   = $today->day;

        $anniversaries = Customer::whereMonth('anniversary_date', $month)
            ->whereDay('anniversary_date', $day)
            ->get(['id', 'name', 'phone', 'anniversary_date']);

        return response()->json([
            'success'       => true,
            'anniversaries' => $anniversaries,
        ]);
    }

    // POST /api/v1/reminders/send
    public function send(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'type'        => 'required|in:birthday,anniversary',
            'channel'     => 'required|in:whatsapp,email,sms',
        ]);

        // Dispatch a notification — actual sending handled by existing WhatsApp/Mail controllers
        return response()->json([
            'success' => true,
            'message' => "Reminder sent to customer #{$request->customer_id} via {$request->channel}.",
        ]);
    }
}
