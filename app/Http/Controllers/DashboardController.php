<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\TeamInvitation;
use App\Models\Transaction;
use App\Services\ReportService;
use App\Services\ShiftService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        public ReportService $reportService,
        public ShiftService $shiftService
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $storeId = $user->store_id ?? 1;

        $email = strtolower($user->email);

        $pendingInvitations = TeamInvitation::query()
            ->with(['inviter', 'team'])
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereNull('accepted_at')
            ->where(fn ($query) => $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>=', now()))
            ->latest()
            ->get()
            ->map(fn (TeamInvitation $invitation) => [
                'code' => $invitation->code,
                'inviterName' => $invitation->inviter->name,
                'team' => [
                    'name' => $invitation->team->name,
                    'slug' => $invitation->team->slug,
                ],
            ]);

        $today = Carbon::today()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();

        // 1. Sales Summary Today
        $todayTransactions = Transaction::where('store_id', $storeId)
            ->where('status', 'completed')
            ->whereDate('transaction_date', $today)
            ->get();

        $todaySalesGross = (float) $todayTransactions->sum('subtotal');
        $todaySalesNet = (float) $todayTransactions->sum('total_amount');
        $todayTransactionCount = $todayTransactions->count();

        // 2. Profit & Loss Month Summary
        $monthlyProfitLoss = $this->reportService->getProfitLossSummary($storeId, $startOfMonth, $today);

        // 3. Low Stock Alert Products
        $lowStockProducts = Product::where('store_id', $storeId)
            ->where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock_alert')
            ->select('id', 'name', 'sku', 'stock', 'min_stock_alert')
            ->get();

        // 4. Recent Transactions
        $recentTransactions = Transaction::where('store_id', $storeId)
            ->with(['user', 'customer'])
            ->latest('transaction_date')
            ->limit(5)
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'invoice_number' => $t->invoice_number,
                'cashier_name' => $t->user?->name ?? 'Kasir',
                'customer_name' => $t->customer?->name ?? 'Umum',
                'total_amount' => (float) $t->total_amount,
                'payment_method' => $t->payment_method,
                'status' => $t->status,
                'created_at' => $t->transaction_date?->toIso8601String(),
            ]);

        // 5. Active Shift
        $activeShift = $this->shiftService->getActiveShift($storeId, $user->id);

        // 6. Top Selling Products Today
        $topProductsToday = $this->reportService->getTopSellingProducts($storeId, 5, $today, $today);

        return Inertia::render('dashboard', [
            'pendingInvitations' => $pendingInvitations,
            'metrics' => [
                'today_sales_gross' => $todaySalesGross,
                'today_sales_net' => $todaySalesNet,
                'today_transaction_count' => $todayTransactionCount,
                'monthly_net_profit' => $monthlyProfitLoss['net_profit'],
                'monthly_revenue' => $monthlyProfitLoss['total_revenue'],
            ],
            'low_stock_products' => $lowStockProducts,
            'recent_transactions' => $recentTransactions,
            'active_shift' => $activeShift ? [
                'id' => $activeShift->id,
                'opened_at' => $activeShift->opened_at?->toIso8601String(),
                'starting_cash' => (float) $activeShift->starting_cash,
            ] : null,
            'top_products' => $topProductsToday,
        ]);
    }
}
