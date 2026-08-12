<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'invoice_number' => $this->invoice_number,
            'transaction_date' => $this->transaction_date?->toIso8601String(),
            'status' => $this->status,
            'subtotal' => (float) $this->subtotal,
            'discount_amount' => (float) $this->discount_amount,
            'tax_amount' => (float) $this->tax_amount,
            'total_amount' => (float) $this->total_amount,
            'paid_amount' => (float) $this->paid_amount,
            'change_amount' => (float) $this->change_amount,
            'payment_method' => $this->payment_method,
            'notes' => $this->notes,
            'cashier' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ],
            'customer' => $this->customer ? [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'phone' => $this->customer->phone,
            ] : null,
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'unit_name' => $item->unit_name,
                    'quantity' => (int) $item->quantity,
                    'sell_price' => (float) $item->sell_price,
                    'discount_amount' => (float) $item->discount_amount,
                    'subtotal' => (float) $item->subtotal,
                ]);
            }),
            'payments' => $this->whenLoaded('payments', function () {
                return $this->payments->map(fn ($pmt) => [
                    'id' => $pmt->id,
                    'payment_method' => $pmt->payment_method,
                    'amount' => (float) $pmt->amount,
                    'reference_number' => $pmt->reference_number,
                ]);
            }),
        ];
    }
}
