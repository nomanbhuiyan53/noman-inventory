<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Point-in-time snapshot of the stock balance.
 *
 * Used to speed up historical balance calculations:
 *   balance_at(T) = latest_snapshot_before(T) + SUM(movements after snapshot)
 *
 * Snapshots are created by the SnapshotStockBalancesCommand (Artisan) or
 * the SnapshotProjector, typically run nightly.
 */
class InventoryStockBalanceSnapshot extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'quantity_on_hand'  => 'float',
            'quantity_reserved' => 'float',
            'avg_cost'          => 'float',
            'total_value'       => 'float',
            'snapshot_at'       => 'datetime',
        ];
    }

    public function getTable(): string
    {
        return config('inventory.tables.stock_balance_snapshots', 'inventory_stock_balance_snapshots');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(InventoryWarehouse::class, 'warehouse_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class, 'batch_id');
    }
}
