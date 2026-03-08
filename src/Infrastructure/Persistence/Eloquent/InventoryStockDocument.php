<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Noman\Inventory\Domain\Shared\Enums\DocumentStatus;

/**
 * Represents a stock document (GRN, Delivery Order, Transfer Order, Adjustment, etc.).
 *
 * Documents are the unit of transactional change. Ledger entries (stock movements)
 * are ONLY created when a document is posted. Reversal creates compensating entries.
 */
class InventoryStockDocument extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'metadata'      => 'array',
            'posted_at'     => 'datetime',
            'approved_at'   => 'datetime',
            'reversed_at'   => 'datetime',
            'cancelled_at'  => 'datetime',
            'status'        => DocumentStatus::class,
        ];
    }

    public function getTable(): string
    {
        return config('inventory.tables.stock_documents', 'inventory_stock_documents');
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function lines(): HasMany
    {
        return $this->hasMany(InventoryStockDocumentLine::class, 'stock_document_id')
            ->orderBy('sort_order');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryStockMovement::class, 'stock_document_id');
    }

    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(InventoryWarehouse::class, 'source_warehouse_id');
    }

    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(InventoryWarehouse::class, 'destination_warehouse_id');
    }

    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(InventoryStockDocument::class, 'reversal_of_id');
    }

    public function reversals(): HasMany
    {
        return $this->hasMany(InventoryStockDocument::class, 'reversal_of_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopePosted(Builder $query): Builder
    {
        return $query->where('status', DocumentStatus::Posted->value);
    }

    public function scopeForTenant(Builder $query, ?string $tenantId): Builder
    {
        return $tenantId ? $query->where('tenant_id', $tenantId) : $query;
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('document_type', $type);
    }

    // -------------------------------------------------------------------------
    // State helpers
    // -------------------------------------------------------------------------

    public function isPosted(): bool
    {
        return $this->status === DocumentStatus::Posted;
    }

    public function canBePosted(): bool
    {
        return $this->status->canBePosted();
    }

    public function canBeReversed(): bool
    {
        return $this->status->canBeReversed();
    }
}
