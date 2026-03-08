<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Noman\Inventory\Domain\Shared\Enums\MovementType;
use Noman\Inventory\Domain\Shared\Enums\ValuationMethod;

/**
 * Append-only valuation entry row.
 * No updated_at; never soft-deleted.
 */
class InventoryValuationEntry extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'quantity'              => 'float',
            'unit_cost'             => 'float',
            'total_cost'            => 'float',
            'running_qty_on_hand'   => 'float',
            'running_avg_cost'      => 'float',
            'running_total_value'   => 'float',
            'movement_type'         => MovementType::class,
            'valuation_method'      => ValuationMethod::class,
        ];
    }

    public function getTable(): string
    {
        return config('inventory.tables.valuation_entries', 'inventory_valuation_entries');
    }

    public function documentLine(): BelongsTo
    {
        return $this->belongsTo(InventoryStockDocumentLine::class, 'stock_document_line_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class, 'batch_id');
    }
}
