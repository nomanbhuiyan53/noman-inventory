<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStockCountEntry extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'expected_quantity'  => 'float',
            'counted_quantity'   => 'float',
            'variance'           => 'float',
            'variance_percentage'=> 'float',
        ];
    }

    public function getTable(): string
    {
        return config('inventory.tables.stock_count_entries', 'inventory_stock_count_entries');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(InventoryStockCountSession::class, 'session_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class, 'batch_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    /**
     * Compute and store the variance when counted_quantity is set.
     */
    public function computeVariance(): void
    {
        if ($this->counted_quantity === null) {
            return;
        }

        $this->variance = $this->counted_quantity - $this->expected_quantity;

        if ($this->expected_quantity != 0) {
            $this->variance_percentage = ($this->variance / $this->expected_quantity) * 100;
        } else {
            $this->variance_percentage = $this->counted_quantity > 0 ? 100.0 : 0.0;
        }
    }

    public function hasVariance(): bool
    {
        return $this->variance !== null && abs($this->variance) > PHP_FLOAT_EPSILON;
    }
}
