<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStockAdjustment extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'adjustment_quantity' => 'float',
            'approved_at'         => 'datetime',
        ];
    }

    public function getTable(): string
    {
        return config('inventory.tables.stock_adjustments', 'inventory_stock_adjustments');
    }

    public function stockDocument(): BelongsTo
    {
        return $this->belongsTo(InventoryStockDocument::class, 'stock_document_id');
    }

    public function stockCountSession(): BelongsTo
    {
        return $this->belongsTo(InventoryStockCountSession::class, 'stock_count_session_id');
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

    public function isPositive(): bool
    {
        return $this->adjustment_quantity > 0;
    }

    public function isNegative(): bool
    {
        return $this->adjustment_quantity < 0;
    }
}
