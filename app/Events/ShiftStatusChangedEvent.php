<?php

namespace App\Events;

use App\Models\CashierShift;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShiftStatusChangedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public CashierShift $shift
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('store.'.$this->shift->store_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'shift.status-changed';
    }

    public function broadcastWith(): array
    {
        return [
            'shift_id' => $this->shift->id,
            'cashier_name' => $this->shift->user?->name,
            'status' => $this->shift->status,
            'opened_at' => $this->shift->opened_at?->toIso8601String(),
            'closed_at' => $this->shift->closed_at?->toIso8601String(),
        ];
    }
}
