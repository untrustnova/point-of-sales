<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan POS Nova</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        h2 { margin-bottom: 5px; }
        .meta { color: #666; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .right { text-align: right; }
        .summary-box { background: #f9f9f9; padding: 15px; border: 1px solid #eee; margin-top: 20px; }
    </style>
</head>
<body>
    <h2>POS Nova - Laporan Penjualan</h2>
    <div class="meta">Periode: {{ $start_date }} s.d. {{ $end_date }}</div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th class="right">Total Transaksi</th>
                <th class="right">Penjualan Kotor</th>
                <th class="right">Diskon</th>
                <th class="right">Penjualan Bersih</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales_report as $row)
            <tr>
                <td>{{ $row->date }}</td>
                <td class="right">{{ number_format($row->total_transactions) }}</td>
                <td class="right">Rp {{ number_format($row->gross_sales, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($row->total_discount, 0, ',', '.') }}</td>
                <td class="right"><strong>Rp {{ number_format($row->net_sales, 0, ',', '.') }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <h3>Ringkasan Laba Rugi</h3>
        <p><strong>Total Pendapatan:</strong> Rp {{ number_format($profit_loss['total_revenue'], 0, ',', '.') }}</p>
        <p><strong>Total HPP / Modal:</strong> Rp {{ number_format($profit_loss['total_cogs'], 0, ',', '.') }}</p>
        <p><strong>Laba Kotor:</strong> Rp {{ number_format($profit_loss['gross_profit'], 0, ',', '.') }}</p>
        <p><strong>Pengeluaran Operasional:</strong> Rp {{ number_format($profit_loss['total_expenses'], 0, ',', '.') }}</p>

        <hr>
        <h4>Estimasi Laba Bersih: Rp {{ number_format($profit_loss['net_profit'], 0, ',', '.') }}</h4>
    </div>
</body>
</html>
