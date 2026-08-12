<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        public int $storeId
    ) {}

    public function collection(): Enumerable
    {
        return Product::where('store_id', $this->storeId)
            ->with(['category', 'unit'])
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'SKU',
            'Barcode',
            'Nama Produk',
            'Kategori',
            'Satuan',
            'Harga Beli',
            'Harga Jual',
            'Stok',
            'Min Alert',
            'Status Active',
        ];
    }

    /**
     * @param  Product  $product
     */
    public function map($product): array
    {
        return [
            $product->id,
            $product->sku,
            $product->barcode,
            $product->name,
            $product->category?->name ?? '-',
            $product->unit?->short_name ?? 'pcs',
            $product->buy_price,
            $product->sell_price,
            $product->stock,
            $product->min_stock_alert,
            $product->is_active ? 'Ya' : 'Tidak',
        ];
    }
}
