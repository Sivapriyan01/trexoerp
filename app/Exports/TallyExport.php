<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TallyExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;
    protected $type;

    public function __construct($data, $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        switch ($this->type) {
            case 'sales':
                return ['Voucher Date', 'Voucher Number', 'Party Name', 'Grand Total', 'Discount', 'Tax Amount', 'Round Off'];
            case 'purchase':
                return ['Voucher Date', 'Voucher Number', 'Party Name', 'Total Amount', 'Tax Amount'];
            case 'customers':
                return ['Name', 'Phone', 'Email', 'GSTIN', 'Address', 'City', 'State', 'Pincode'];
            case 'products':
                return ['Product Name', 'Unit', 'HSN', 'MRP', 'Dealer Price', 'Stock'];
            default:
                return [];
        }
    }

    public function map($row): array
    {
        switch ($this->type) {
            case 'sales':
                return [
                    $row->bill_date ? $row->bill_date->format('Y-m-d') : '',
                    $row->invoice_no,
                    $row->customer_name ?: 'Cash',
                    $row->grand_total,
                    $row->discount_amount,
                    $row->gst_amount,
                    $row->round_off,
                ];
            case 'purchase':
                return [
                    $row->invoice_date ? $row->invoice_date->format('Y-m-d') : '',
                    $row->invoice_ref,
                    $row->vendor ? $row->vendor->name : 'Unknown Supplier',
                    $row->total_amount,
                    $row->gst_amount,
                ];
            case 'customers':
                return [
                    $row->name,
                    $row->phone,
                    $row->email,
                    $row->gstin,
                    $row->address,
                    $row->city,
                    $row->state,
                    $row->pincode,
                ];
            case 'products':
                return [
                    $row->product_name,
                    $row->unit,
                    $row->hsn,
                    $row->mrp,
                    $row->dealer_price,
                    $row->stock,
                ];
            default:
                return [];
        }
    }
}
