<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryCustomField extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'options'     => 'array',
            'is_required' => 'boolean',
            'is_active'   => 'boolean',
        ];
    }

    public function getTable(): string
    {
        return config('inventory.tables.custom_fields', 'inventory_custom_fields');
    }

    public function values(): HasMany
    {
        return $this->hasMany(InventoryCustomFieldValue::class, 'custom_field_id');
    }
}
