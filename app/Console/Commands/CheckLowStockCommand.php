<?php

namespace App\Console\Commands;

use App\Events\LowStockAlertEvent;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;

class CheckLowStockCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pos:check-low-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pindai seluruh produk dengan stok menipis dan kirimkan notifikasi realtime via Laravel Reverb';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $lowStockProducts = Product::where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock_alert')
            ->get();

        $count = 0;

        foreach ($lowStockProducts as $product) {
            Event::dispatch(new LowStockAlertEvent($product));
            $count++;
        }

        $this->info("Berhasil mengirimkan {$count} alert stok menipis via Reverb.");

        return Command::SUCCESS;
    }
}
