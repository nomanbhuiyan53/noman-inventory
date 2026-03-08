<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryInventoryAging extends Model
{
    public const CREATED_AT = null;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'qty_0_to_30_days'  => 'float',
            'qty_31_to_60_days' => 'float',
            'qty_61_to_90_days' => 'float',
            'qty_over_90_days'  => 'float',
            'total_quantity'    => 'float',
        ];
    }

    public function getTable(): string
    {
        return config('inventory.tables.inventory_aging', 'inventory_inventory_aging');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(InventoryWarehouse::class, 'warehouse_id');
    }
}
