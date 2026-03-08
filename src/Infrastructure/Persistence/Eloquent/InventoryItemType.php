<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Noman\Inventory\Domain\Shared\Enums\IndustryProfile;

class InventoryItemType extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'policy_overrides' => 'array',
            'is_active'        => 'boolean',
            'industry_profile' => IndustryProfile::class,
        ];
    }

    public function getTable(): string
    {
        return config('inventory.tables.item_types', 'inventory_item_types');
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'item_type_id');
    }
}
