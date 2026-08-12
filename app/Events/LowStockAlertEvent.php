<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockAlertEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Product $product
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('store.'.$this->product->store_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'product.low-stock';
    }

    public function broadcastWith(): array
    {
        return [
            'product_id' => $this->product->id,
            'name' => $this->product->name,
            'current_stock' => $this->product->stock,
            'min_stock_alert' => $this->product->min_stock_alert,
        ];
    }
}
