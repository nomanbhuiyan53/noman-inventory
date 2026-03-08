<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryLocation extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'metadata'  => 'array',
        ];
    }

    public function getTable(): string
    {
        return config('inventory.tables.locations', 'inventory_locations');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(InventoryWarehouse::class, 'warehouse_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(InventoryLocation::class, 'parent_id');
    }

    public function stockBalances(): HasMany
    {
        return $this->hasMany(InventoryStockBalance::class, 'location_id');
    }
}
