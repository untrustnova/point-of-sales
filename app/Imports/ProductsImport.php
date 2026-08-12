<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow
{
    public function __construct(
        public int $storeId
    ) {}

    public function model(array $row)
    {
        if (empty($row['nama_produk'])) {
            return null;
        }

        $name = trim($row['nama_produk']);
        $sku = ! empty($row['sku']) ? trim($row['sku']) : ('PRD-'.Str::upper(Str::random(6)));

        return new Product([
            'store_id' => $this->storeId,
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(4),
            'sku' => $sku,
            'barcode' => ! empty($row['barcode']) ? trim($row['barcode']) : $sku,
            'buy_price' => (float) ($row['harga_beli'] ?? 0),
            'sell_price' => (float) ($row['harga_jual'] ?? 0),
            'stock' => (int) ($row['stok'] ?? 0),
            'min_stock_alert' => (int) ($row['min_alert'] ?? 5),
            'is_active' => true,
        ]);
    }
}
