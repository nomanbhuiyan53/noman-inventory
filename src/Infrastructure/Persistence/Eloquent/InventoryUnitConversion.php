<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryUnitConversion extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'factor' => 'float',
        ];
    }

    public function getTable(): string
    {
        return config('inventory.tables.unit_conversions', 'inventory_unit_conversions');
    }

    public function fromUnit(): BelongsTo
    {
        return $this->belongsTo(InventoryUnit::class, 'from_unit_id');
    }

    public function toUnit(): BelongsTo
    {
        return $this->belongsTo(InventoryUnit::class, 'to_unit_id');
    }
}
