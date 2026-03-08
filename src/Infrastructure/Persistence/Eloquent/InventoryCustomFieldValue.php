<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryCustomFieldValue extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return config('inventory.tables.custom_field_values', 'inventory_custom_field_values');
    }

    public function customField(): BelongsTo
    {
        return $this->belongsTo(InventoryCustomField::class, 'custom_field_id');
    }
}
