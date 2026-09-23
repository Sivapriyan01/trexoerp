<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bill;
use App\Services\WhatsappService;
use App\Models\Setting;
use Illuminate\Support\Carbon;

class SendBillReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bill:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send anniversary reminders to customers via WhatsApp';

    /**
     * Execute the console command.
     */
    public function handle(WhatsappService $whatsapp)
    {
        $today = Carbon::today();
        $month = $today->month;
        $day = $today->day;

        $this->info("Checking for anniversary reminders for {$today->format('d M')}...");

        $bills = Bill::where('reminder_enabled', true)
            ->whereMonth('bill_date', $month)
            ->whereDay('bill_date', $day)
            ->whereYear('bill_date', '<', $today->year) // Only previous years
            ->get();

        if ($bills->isEmpty()) {
            $this->info("No reminders to send today.");
            return;
        }

        $businessName = Setting::get('business_name', config('app.name'));
        $messageTemplate = Setting::get('anniversary_reminder_message', "🌟 *Special Anniversary Greeting from {business_name}* 🌟\n\nHello {customer_name},\n\nIt has been exactly {years} {unit} since your visit to our store on *{date}* (Invoice #{invoice_no}).\n\nWe truly appreciate your continued trust in us. We would love to see you again soon! ✨\n\nHave a wonderful day! 🙏");

        foreach ($bills as $bill) {
            if (!$bill->customer_phone) {
                continue;
            }

            $customerName = $bill->customer_name ?: 'Customer';
            $years = $today->year - $bill->bill_date->year;
            $unit = ($years === 1) ? 'year' : 'years';

            $message = str_replace(
                ['{business_name}', '{customer_name}', '{years}', '{unit}', '{date}', '{invoice_no}'],
                [$businessName, $customerName, $years, $unit, $bill->bill_date->format('d M, Y'), $bill->invoice_no],
                $messageTemplate
            );

            $this->info("Sending reminder to {$customerName} ({$bill->customer_phone})...");
            
            $result = $whatsapp->sendMessage($bill->customer_phone, $message);

            if ($result['success']) {
                $this->info("Successfully sent!");
            } else {
                $this->error("Failed to send: " . ($result['message'] ?? 'Unknown error'));
            }
        }

        $this->info("All bill reminders processed.");

        // Part 2: Customer Anniversaries (Birthdays, etc.)
        $this->info("Checking for Customer profile anniversaries...");
        $customers = \App\Models\Customer::where('anniversary_reminder_enabled', true)
            ->whereMonth('anniversary_date', $month)
            ->whereDay('anniversary_date', $day)
            ->get();

        foreach ($customers as $customer) {
            $anniversaryDate = Carbon::parse($customer->anniversary_date);
            $years = $today->year - $anniversaryDate->year;
            $unit = ($years === 1) ? 'year' : 'years';

            $message = str_replace(
                ['{business_name}', '{customer_name}', '{years}', '{unit}', '{date}', '{invoice_no}'],
                [$businessName, $customer->name, $years, $unit, $anniversaryDate->format('d M, Y'), 'N/A'],
                $messageTemplate
            );

            $this->info("Sending anniversary greeting to {$customer->name}...");
            $whatsapp->sendMessage($customer->phone, $message);
        }

        $this->info("All reminders processed.");
    }

    private function getOrdinal($number)
    {
        if ($number === 1) return 'year';
        return 'years';
    }
}
