<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryStockDocumentLine extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'serial_ids' => 'array',
            'quantity'   => 'float',
            'unit_cost'  => 'float',
            'total_cost' => 'float',
        ];
    }

    public function getTable(): string
    {
        return config('inventory.tables.stock_document_lines', 'inventory_stock_document_lines');
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function document(): BelongsTo
    {
        return $this->belongsTo(InventoryStockDocument::class, 'stock_document_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(InventoryItemVariant::class, 'variant_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class, 'batch_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(InventoryWarehouse::class, 'warehouse_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(InventoryUnit::class, 'unit_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryStockMovement::class, 'stock_document_line_id');
    }

    public function valuationEntries(): HasMany
    {
        return $this->hasMany(InventoryValuationEntry::class, 'stock_document_line_id');
    }
}
