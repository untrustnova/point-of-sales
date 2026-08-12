<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk #{{ $transaction->invoice_number }}</title>
    <style>
        @page {
            margin: 0;
            size: 80mm auto;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            width: 72mm;
            margin: 0 auto;
            padding: 5px;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; }
        .qr-container { margin-top: 10px; text-align: center; }
        .qr-container svg { width: 90px; height: 90px; }
    </style>
</head>
<body>
    <div class="text-center">
        <div class="bold" style="font-size: 14px;">{{ $store->name }}</div>
        <div>{{ $store->address }}</div>
        <div>Telp: {{ $store->phone }}</div>
        @if($store->tax_number)
            <div>NPWP: {{ $store->tax_number }}</div>
        @endif
    </div>

    <div class="divider"></div>

    <table>
        <tr>
            <td>No: {{ $transaction->invoice_number }}</td>
            <td class="text-right">{{ $transaction->transaction_date->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Kasir: {{ $transaction->user?->name ?? 'Kasir' }}</td>
            <td class="text-right">Plg: {{ $transaction->customer?->name ?? 'Umum' }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table>
        @foreach($transaction->items as $item)
            <tr>
                <td colspan="2" class="bold">{{ $item->product_name }}</td>
            </tr>
            <tr>
                <td>{{ $item->quantity }} x {{ number_format($item->sell_price, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td>Subtotal</td>
            <td class="text-right">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</td>
        </tr>
        @if($transaction->discount_amount > 0)
        <tr>
            <td>Diskon</td>
            <td class="text-right">- Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if($transaction->tax_amount > 0)
        <tr>
            <td>Pajak (PPN)</td>
            <td class="text-right">+ Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr class="bold">
            <td>TOTAL</td>
            <td class="text-right">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Bayar ({{ strtoupper($transaction->payment_method) }})</td>
            <td class="text-right">Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Kembali</td>
            <td class="text-right">Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="qr-container">
        {!! $qr_code !!}
        <div style="font-size: 9px; margin-top: 2px;">Scan untuk Struk Digital</div>
    </div>

    <div class="divider"></div>

    <div class="text-center" style="margin-top: 5px;">
        <div>{{ $store->settings['receipt_footer'] ?? 'Terima Kasih Atas Kunjungan Anda!' }}</div>
        <div style="font-size: 9px; margin-top: 3px;">Powered by POS Nova</div>
    </div>
</body>
</html>
