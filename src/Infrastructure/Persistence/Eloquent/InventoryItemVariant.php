<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItemVariant extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'attributes'       => 'array',
            'is_active'        => 'boolean',
            'price_adjustment' => 'float',
            'cost_adjustment'  => 'float',
        ];
    }

    public function getTable(): string
    {
        return config('inventory.tables.item_variants', 'inventory_item_variants');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }
}
