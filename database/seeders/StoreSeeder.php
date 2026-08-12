<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $store = Store::firstOrCreate(
            ['code' => 'STORE-001'],
            [
                'name' => 'Toko POS Nova Pusat',
                'address' => 'Jl. Merdeka Utama No. 45, Jakarta Pusat',
                'phone' => '081234567890',
                'email' => 'info@posnova.test',
                'tax_number' => '01.234.567.8-901.000',
                'is_active' => true,
            ]
        );

        $warehouse = Warehouse::firstOrCreate(
            ['store_id' => $store->id, 'name' => 'Gudang Utama'],
            [
                'address' => 'Jl. Merdeka Utama No. 45 (Lantai 2)',
                'is_default' => true,
                'is_active' => true,
            ]
        );

        // Master Data: Units
        $unitPcs = Unit::firstOrCreate(['store_id' => $store->id, 'short_name' => 'pcs'], ['name' => 'Pieces']);
        $unitBox = Unit::firstOrCreate(['store_id' => $store->id, 'short_name' => 'box'], ['name' => 'Box']);

        // Master Data: Categories
        $catFood = Category::firstOrCreate(['store_id' => $store->id, 'slug' => 'makanan-minuman'], ['name' => 'Makanan & Minuman']);
        $catElectronics = Category::firstOrCreate(['store_id' => $store->id, 'slug' => 'elektronik'], ['name' => 'Elektronik & Aksesoris']);

        // Master Data: Products
        $p1 = Product::firstOrCreate(
            ['store_id' => $store->id, 'sku' => 'PRD-001'],
            [
                'category_id' => $catFood->id,
                'unit_id' => $unitPcs->id,
                'name' => 'Kopi Susu Gula Aren 250ml',
                'slug' => 'kopi-susu-gula-aren-250ml',
                'barcode' => '8991234567891',
                'buy_price' => 10000,
                'sell_price' => 18000,
                'stock' => 50,
                'min_stock_alert' => 10,
                'is_active' => true,
            ]
        );

        $p2 = Product::firstOrCreate(
            ['store_id' => $store->id, 'sku' => 'PRD-002'],
            [
                'category_id' => $catFood->id,
                'unit_id' => $unitBox->id,
                'name' => 'Roti Bakar Cokelat Keju',
                'slug' => 'roti-bakar-cokelat-keju',
                'barcode' => '8991234567892',
                'buy_price' => 15000,
                'sell_price' => 25000,
                'stock' => 30,
                'min_stock_alert' => 5,
                'is_active' => true,
            ]
        );

        // Master Data: Customer & Supplier
        Customer::firstOrCreate(
            ['store_id' => $store->id, 'phone' => '089876543210'],
            [
                'name' => 'Pelanggan Setia Budi',
                'email' => 'budi@gmail.test',
                'address' => 'Jl. Sudirman No. 12',
                'points' => 120,
            ]
        );

        Supplier::firstOrCreate(
            ['store_id' => $store->id, 'email' => 'supplier@beverage.test'],
            [
                'name' => 'PT Distribusi Minuman Sejahtera',
                'contact_name' => 'Hendra',
                'phone' => '0215551234',
                'address' => 'Kawasan Industri Pulogadung',
            ]
        );
    }
}
