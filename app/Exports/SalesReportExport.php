<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesReportExport implements FromCollection, WithHeadings
{
    public function __construct(
        public Collection $salesData
    ) {}

    public function collection(): Enumerable
    {
        return $this->salesData->map(fn ($row) => [
            'date' => $row->date,
            'total_transactions' => $row->total_transactions,
            'gross_sales' => $row->gross_sales,
            'total_discount' => $row->total_discount,
            'tax_amount' => $row->total_tax,
            'net_sales' => $row->net_sales,
        ]);
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Jumlah Transaksi',
            'Penjualan Kotor (Rp)',
            'Total Diskon (Rp)',
            'Total Pajak (Rp)',
            'Penjualan Bersih (Rp)',
        ];
    }
}
