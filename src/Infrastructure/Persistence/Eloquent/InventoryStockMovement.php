<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Noman\Inventory\Domain\Shared\Enums\MovementType;

/**
 * The core append-only ledger row.
 *
 * This model is NEVER updated or soft-deleted after creation.
 * All inventory balance calculations derive from this table.
 *
 * The `quantity` column is signed:
 *   - Positive = stock increase (inbound)
 *   - Negative = stock decrease (outbound)
 */
class InventoryStockMovement extends Model
{
    public const UPDATED_AT = null; // append-only: no updated_at

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'movement_type' => MovementType::class,
            'quantity'      => 'float',
            'unit_cost'     => 'float',
            'total_cost'    => 'float',
            'metadata'      => 'array',
            'posted_at'     => 'datetime',
        ];
    }

    public function getTable(): string
    {
        return config('inventory.tables.stock_movements', 'inventory_stock_movements');
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function document(): BelongsTo
    {
        return $this->belongsTo(InventoryStockDocument::class, 'stock_document_id');
    }

    public function documentLine(): BelongsTo
    {
        return $this->belongsTo(InventoryStockDocumentLine::class, 'stock_document_line_id');
    }

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

    public function serial(): BelongsTo
    {
        return $this->belongsTo(InventorySerialNumber::class, 'serial_id');
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

    public function scopeForTenant(Builder $query, ?string $tenantId): Builder
    {
        return $tenantId ? $query->where('tenant_id', $tenantId) : $query;
    }

    public function scopeInDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->where('posted_at', '>=', $from);
        }

        if ($to) {
            $query->where('posted_at', '<=', $to);
        }

        return $query;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isInbound(): bool
    {
        return $this->quantity > 0;
    }

    public function isOutbound(): bool
    {
        return $this->quantity < 0;
    }
}
