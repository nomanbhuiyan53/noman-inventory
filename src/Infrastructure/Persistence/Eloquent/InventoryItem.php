<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Noman\Inventory\Domain\Shared\Enums\IndustryProfile;

/**
 * Eloquent model for inventory_items.
 *
 * Represents the "stock master" — the catalogue record for every item
 * that can be received, issued, transferred, or counted in the inventory.
 *
 * Host applications should NOT extend this model for domain-specific fields.
 * Use the custom_fields mechanism or attach domain metadata via the
 * extensibility hooks described in the README.
 */
class InventoryItem extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'policy_overrides'  => 'array',
            'metadata'          => 'array',
            'is_active'         => 'boolean',
            'is_purchasable'    => 'boolean',
            'is_saleable'       => 'boolean',
            'is_stockable'      => 'boolean',
            'standard_cost'     => 'float',
            'selling_price'     => 'float',
            'reorder_level'     => 'float',
            'reorder_quantity'  => 'float',
            'min_stock_level'   => 'float',
            'max_stock_level'   => 'float',
            'industry_profile'  => IndustryProfile::class,
        ];
    }

    public function getTable(): string
    {
        return config('inventory.tables.items', 'inventory_items');
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function itemType(): BelongsTo
    {
        return $this->belongsTo(InventoryItemType::class, 'item_type_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(InventoryUnit::class, 'unit_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(InventoryItemVariant::class, 'item_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class, 'item_id');
    }

    public function serialNumbers(): HasMany
    {
        return $this->hasMany(InventorySerialNumber::class, 'item_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(InventoryStockMovement::class, 'item_id');
    }

    public function stockBalances(): HasMany
    {
        return $this->hasMany(InventoryStockBalance::class, 'item_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(InventoryReservation::class, 'item_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            InventoryTag::class,
            config('inventory.tables.item_tag_maps', 'inventory_item_tag_maps'),
            'item_id',
            'tag_id'
        )->withTimestamps();
    }

    public function customFieldValues(): HasMany
    {
        return $this->hasMany(InventoryCustomFieldValue::class, 'entity_id')
            ->where('entity_type', 'item');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeStockable(Builder $query): Builder
    {
        return $query->where('is_stockable', true);
    }

    public function scopeForTenant(Builder $query, ?string $tenantId): Builder
    {
        if ($tenantId === null) {
            return $query;
        }

        return $query->where('tenant_id', $tenantId);
    }
}
