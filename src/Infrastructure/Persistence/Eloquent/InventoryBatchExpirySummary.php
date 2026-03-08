<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBatchExpirySummary extends Model
{
    public const CREATED_AT = null;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'quantity_on_hand'  => 'float',
            'expiry_date'       => 'date',
            'days_until_expiry' => 'integer',
            'is_expired'        => 'boolean',
        ];
    }

    public function getTable(): string
    {
        return config('inventory.tables.batch_expiry_summary', 'inventory_batch_expiry_summary');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class, 'batch_id');
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
