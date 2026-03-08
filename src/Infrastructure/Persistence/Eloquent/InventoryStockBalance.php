<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The denormalised stock balance projection.
 *
 * This table is NEVER directly written to by Actions.
 * It is maintained exclusively by projection Listeners (Phase 5) that
 * react to domain events (StockReceived, StockIssued, etc.).
 *
 * For queries, this is the primary table to read current stock levels.
 * For balance-correctness audits, re-derive from InventoryStockMovement.
 */
class InventoryStockBalance extends Model
{
    public const CREATED_AT = null;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'quantity_on_hand'  => 'float',
            'quantity_reserved' => 'float',
            'quantity_available'=> 'float',
            'avg_cost'          => 'float',
            'total_value'       => 'float',
            'last_movement_at'  => 'datetime',
        ];
    }

    public function getTable(): string
    {
        return config('inventory.tables.stock_balances', 'inventory_stock_balances');
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(InventoryWarehouse::class, 'warehouse_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class, 'batch_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeForItem(Builder $query, string $itemId): Builder
    {
        return $query->where('item_id', $itemId);
    }

    public function scopeForWarehouse(Builder $query, string $warehouseId): Builder
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeWithPositiveStock(Builder $query): Builder
    {
        return $query->where('quantity_on_hand', '>', 0);
    }

    public function scopeForTenant(Builder $query, ?string $tenantId): Builder
    {
        return $tenantId ? $query->where('tenant_id', $tenantId) : $query;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function getAvailableQuantity(): float
    {
        return max(0.0, $this->quantity_on_hand - $this->quantity_reserved);
    }
}
