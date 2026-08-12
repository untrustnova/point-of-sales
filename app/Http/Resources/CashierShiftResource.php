<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashierShiftResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'opened_at' => $this->opened_at?->toIso8601String(),
            'closed_at' => $this->closed_at?->toIso8601String(),
            'starting_cash' => (float) $this->starting_cash,
            'ending_cash_expected' => (float) $this->ending_cash_expected,
            'ending_cash_actual' => $this->ending_cash_actual !== null ? (float) $this->ending_cash_actual : null,
            'cash_difference' => $this->cash_difference !== null ? (float) $this->cash_difference : null,
            'status' => $this->status,
            'notes' => $this->notes,
            'cashier' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ],
        ];
    }
}
