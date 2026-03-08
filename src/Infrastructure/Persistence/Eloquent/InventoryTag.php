<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InventoryTag extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return config('inventory.tables.tags', 'inventory_tags');
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(
            InventoryItem::class,
            config('inventory.tables.item_tag_maps', 'inventory_item_tag_maps'),
            'tag_id',
            'item_id'
        )->withTimestamps();
    }
}
