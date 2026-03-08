<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryStockCountSession extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'count_date'   => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function getTable(): string
    {
        return config('inventory.tables.stock_count_sessions', 'inventory_stock_count_sessions');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(InventoryWarehouse::class, 'warehouse_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(InventoryStockCountEntry::class, 'session_id');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(InventoryStockAdjustment::class, 'stock_count_session_id');
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
