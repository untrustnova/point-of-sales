<?php

namespace App\Services;

use Linkxtr\QrCode\Facades\QrCode;

class QrCodeService
{
    /**
     * Menghasilkan string SVG QR Code dari payload data.
     */
    public function generateSvg(string $data, int $size = 200): string
    {
        return QrCode::generate($data);
    }

    /**
     * Menghasilkan URL QR Code Struk digital transaksi.
     */
    public function generateReceiptQrCode(string $invoiceNumber): string
    {
        $url = route('pos.index').'?invoice='.urlencode($invoiceNumber);

        return $this->generateSvg($url, 180);
    }
}
