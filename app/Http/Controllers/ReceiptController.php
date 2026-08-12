<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Transaction;
use App\Services\QrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReceiptController extends Controller
{
    public function __construct(
        public QrCodeService $qrCodeService
    ) {}

    public function show(Request $request, Transaction $transaction): Response
    {
        $transaction->load(['items', 'payments', 'user', 'customer']);
        $store = Store::findOrFail($transaction->store_id);
        $qrCodeSvg = $this->qrCodeService->generateReceiptQrCode($transaction->invoice_number);

        return Inertia::render('receipt/show', [
            'transaction' => $transaction,
            'store' => $store,
            'qr_code_svg' => $qrCodeSvg,
        ]);
    }

    public function downloadPdf(Request $request, Transaction $transaction): HttpResponse
    {
        $transaction->load(['items', 'payments', 'user', 'customer']);
        $store = Store::findOrFail($transaction->store_id);
        $qrCodeSvg = $this->qrCodeService->generateReceiptQrCode($transaction->invoice_number);

        $pdf = Pdf::loadView('pdf.receipt', [
            'transaction' => $transaction,
            'store' => $store,
            'qr_code' => $qrCodeSvg,
        ]);

        $safeInvoice = str_replace(['/', '\\'], '-', $transaction->invoice_number);

        return $pdf->download("Struk_{$safeInvoice}.pdf");
    }
}
