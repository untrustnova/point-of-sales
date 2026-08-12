<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    protected $fillable = [
        'store_id',
        'category_id',
        'unit_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'description',
        'image_path',
        'buy_price',
        'sell_price',
        'wholesale_price',
        'stock',
        'min_stock_alert',
        'has_variants',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'buy_price' => 'decimal:2',
            'sell_price' => 'decimal:2',
            'wholesale_price' => 'decimal:2',
            'stock' => 'integer',
            'min_stock_alert' => 'integer',
            'has_variants' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => (int) $this->id,
            'store_id' => (int) $this->store_id,
            'name' => (string) $this->name,
            'sku' => (string) $this->sku,
            'barcode' => (string) $this->barcode,
            'description' => (string) $this->description,
            'category_name' => (string) ($this->category?->name ?? ''),
        ];
    }
}
