<?php

namespace App\Http\Controllers;

use App\Exports\SalesReportExport;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(
        public ReportService $reportService
    ) {}

    public function sales(Request $request): Response
    {
        $user = $request->user();
        $storeId = $user->store_id ?? 1;

        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $salesReport = $this->reportService->getSalesReport($storeId, $startDate, $endDate);
        $topProducts = $this->reportService->getTopSellingProducts($storeId, 5, $startDate, $endDate);
        $profitLoss = $this->reportService->getProfitLossSummary($storeId, $startDate, $endDate);

        return Inertia::render('reports/sales', [
            'sales_report' => $salesReport,
            'top_products' => $topProducts,
            'profit_loss' => $profitLoss,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    public function downloadPdf(Request $request): HttpResponse
    {
        $user = $request->user();
        $storeId = $user->store_id ?? 1;

        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $salesReport = $this->reportService->getSalesReport($storeId, $startDate, $endDate);
        $profitLoss = $this->reportService->getProfitLossSummary($storeId, $startDate, $endDate);

        $pdf = Pdf::loadView('pdf.sales-report', [
            'sales_report' => $salesReport,
            'profit_loss' => $profitLoss,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return $pdf->download("Laporan_Penjualan_{$startDate}_sampai_{$endDate}.pdf");
    }

    public function downloadExcel(Request $request)
    {
        $user = $request->user();
        $storeId = $user->store_id ?? 1;

        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $salesReport = $this->reportService->getSalesReport($storeId, $startDate, $endDate);

        return Excel::download(
            new SalesReportExport($salesReport),
            "Laporan_Penjualan_{$startDate}_sampai_{$endDate}.xlsx"
        );
    }
}
