<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryBatch extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'manufacture_date'  => 'date',
            'expiry_date'       => 'date',
            'quantity_received' => 'float',
            'unit_cost'         => 'float',
            'metadata'          => 'array',
        ];
    }

    public function getTable(): string
    {
        return config('inventory.tables.batches', 'inventory_batches');
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function serialNumbers(): HasMany
    {
        return $this->hasMany(InventorySerialNumber::class, 'batch_id');
    }

    public function stockBalances(): HasMany
    {
        return $this->hasMany(InventoryStockBalance::class, 'batch_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('expiry_date')
              ->orWhere('expiry_date', '>=', now()->toDateString());
        });
    }

    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [
                now()->toDateString(),
                now()->addDays($days)->toDateString(),
            ]);
    }

    public function scopeOrderByFefo(Builder $query): Builder
    {
        return $query->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END, expiry_date ASC');
    }

    public function scopeOrderByFifo(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'asc');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        if ($this->expiry_date === null) {
            return false;
        }

        return $this->expiry_date->diffInDays(now()) <= $days && ! $this->isExpired();
    }
}
