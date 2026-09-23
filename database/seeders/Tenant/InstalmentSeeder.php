<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use App\Models\Instalment;
use App\Models\InstalmentSchedule;
use App\Models\Bill;
use App\Models\Customer;

class InstalmentSeeder extends Seeder
{
    public function run()
    {
        $customers = Customer::limit(3)->get();
        
        foreach ($customers as $index => $customer) {
            $bill = Bill::where('customer_id', $customer->id)->first() ?? Bill::first();
            if (!$bill) continue;

            $total = $bill->grand_total > 0 ? $bill->grand_total : 5000;
            $advance = $total * 0.2;
            $balance = $total - $advance;
            $insCount = 5;
            $insAmt = $balance / $insCount;

            $instalment = Instalment::create([
                'bill_id' => $bill->id,
                'customer_id' => $customer->id,
                'total_amount' => $total,
                'paid_amount' => $advance,
                'due_amount' => $balance,
                'status' => $index == 1 ? 'overdue' : 'pending',
                'next_due_date' => now()->addDays(15)
            ]);

            // Advance
            InstalmentSchedule::create([
                'instalment_id' => $instalment->id,
                'instalment_no' => 0,
                'type' => 'advance',
                'due_date' => $bill->bill_date ?? now(),
                'amount' => $advance,
                'status' => 'paid',
                'paid_at' => now()
            ]);

            // Instalments
            for ($i = 1; $i <= $insCount; $i++) {
                InstalmentSchedule::create([
                    'instalment_id' => $instalment->id,
                    'instalment_no' => $i,
                    'type' => 'instalment',
                    'due_date' => now()->addMonths($i),
                    'amount' => $insAmt,
                    'status' => ($index == 1 && $i == 1) ? 'overdue' : 'pending'
                ]);
            }
        }
    }
}
