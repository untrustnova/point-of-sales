<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],
            'unit' => [
                'id' => $this->unit?->id,
                'name' => $this->unit?->name,
                'short_name' => $this->unit?->short_name,
            ],
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'description' => $this->description,
            'image_url' => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'buy_price' => (float) $this->buy_price,
            'sell_price' => (float) $this->sell_price,
            'wholesale_price' => $this->wholesale_price ? (float) $this->wholesale_price : null,
            'stock' => (int) $this->stock,
            'min_stock_alert' => (int) $this->min_stock_alert,
            'has_variants' => (bool) $this->has_variants,
            'is_active' => (bool) $this->is_active,
            'variants' => $this->whenLoaded('variants', function () {
                return $this->variants->map(fn ($v) => [
                    'id' => $v->id,
                    'name' => $v->name,
                    'sku' => $v->sku,
                    'barcode' => $v->barcode,
                    'buy_price' => $v->buy_price ? (float) $v->buy_price : (float) $this->buy_price,
                    'sell_price' => $v->sell_price ? (float) $v->sell_price : (float) $this->sell_price,
                    'stock' => (int) $v->stock,
                    'is_active' => (bool) $v->is_active,
                ]);
            }),
        ];
    }
}
