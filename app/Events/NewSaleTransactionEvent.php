<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewSaleTransactionEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Transaction $transaction
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('store.'.$this->transaction->store_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'transaction.new-sale';
    }

    public function broadcastWith(): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'invoice_number' => $this->transaction->invoice_number,
            'total_amount' => (float) $this->transaction->total_amount,
            'payment_method' => $this->transaction->payment_method,
            'cashier_name' => $this->transaction->user?->name ?? 'Kasir',
            'created_at' => $this->transaction->created_at?->toIso8601String(),
        ];
    }
}
