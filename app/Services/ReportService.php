<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Laporan ringkasan penjualan harian/rentang tanggal menggunakan DB::table untuk performa optimal.
     */
    public function getSalesReport(int $storeId, string $startDate, string $endDate): Collection
    {
        return DB::table('transactions')
            ->where('store_id', $storeId)
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->select(
                DB::raw('DATE(transaction_date) as date'),
                DB::raw('COUNT(id) as total_transactions'),
                DB::raw('SUM(subtotal) as gross_sales'),
                DB::raw('SUM(discount_amount) as total_discount'),
                DB::raw('SUM(tax_amount) as total_tax'),
                DB::raw('SUM(total_amount) as net_sales')
            )
            ->groupBy(DB::raw('DATE(transaction_date)'))
            ->orderBy('date', 'ASC')
            ->get();
    }

    /**
     * Produk paling laris berdasarkan kuantitas terjuat.
     */
    public function getTopSellingProducts(int $storeId, int $limit = 10, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.store_id', $storeId)
            ->where('transactions.status', 'completed');

        if ($startDate && $endDate) {
            $query->whereBetween('transactions.transaction_date', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        }

        return $query->select(
            'transaction_items.product_id',
            'transaction_items.product_name',
            DB::raw('SUM(transaction_items.quantity) as total_quantity_sold'),
            DB::raw('SUM(transaction_items.subtotal) as total_revenue')
        )
            ->groupBy('transaction_items.product_id', 'transaction_items.product_name')
            ->orderByDesc('total_quantity_sold')
            ->limit($limit)
            ->get();
    }

    /**
     * Laporan estimasi Laba Rugi sederhana.
     */
    public function getProfitLossSummary(int $storeId, string $startDate, string $endDate): array
    {
        $salesSummary = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.store_id', $storeId)
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.transaction_date', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->select(
                DB::raw('SUM(transaction_items.subtotal) as total_revenue'),
                DB::raw('SUM(transaction_items.quantity * transaction_items.buy_price) as total_cogs')
            )
            ->first();

        $totalExpenses = DB::table('expenses')
            ->where('store_id', $storeId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $revenue = (float) ($salesSummary?->total_revenue ?? 0);
        $cogs = (float) ($salesSummary?->total_cogs ?? 0);
        $grossProfit = $revenue - $cogs;
        $netProfit = $grossProfit - (float) $totalExpenses;

        return [
            'total_revenue' => $revenue,
            'total_cogs' => $cogs,
            'gross_profit' => $grossProfit,
            'total_expenses' => (float) $totalExpenses,
            'net_profit' => $netProfit,
        ];
    }
}
