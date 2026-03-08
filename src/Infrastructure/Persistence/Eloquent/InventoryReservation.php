<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryReservation extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'quantity'     => 'float',
            'metadata'     => 'array',
            'expires_at'   => 'datetime',
            'released_at'  => 'datetime',
        ];
    }

    public function getTable(): string
    {
        return config('inventory.tables.reservations', 'inventory_reservations');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(InventoryWarehouse::class, 'warehouse_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now());
    }

    public function scopeForTenant(Builder $query, ?string $tenantId): Builder
    {
        return $tenantId ? $query->where('tenant_id', $tenantId) : $query;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
