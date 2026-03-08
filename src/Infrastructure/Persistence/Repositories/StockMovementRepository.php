<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockMovement;

/**
 * Repository for reading the append-only stock movement ledger.
 *
 * No write methods are provided here; movements are created exclusively by
 * the PostStockDocumentAction to maintain append-only integrity.
 */
class StockMovementRepository
{
    /**
     * Compute the current stock balance for an item by summing all ledger entries.
     * This is the authoritative balance computation from the ledger.
     * For performance, prefer reading from InventoryStockBalance (projection).
     */
    public function computeBalance(
        string $itemId,
        ?string $warehouseId = null,
        ?string $locationId = null,
        ?string $batchId = null,
        ?string $tenantId = null,
    ): Quantity {
        $sum = InventoryStockMovement::query()
            ->forTenant($tenantId)
            ->forItem($itemId)
            ->when($warehouseId, fn (Builder $q) => $q->where('warehouse_id', $warehouseId))
            ->when($locationId,  fn (Builder $q) => $q->where('location_id', $locationId))
            ->when($batchId,     fn (Builder $q) => $q->where('batch_id', $batchId))
            ->sum('quantity');

        return Quantity::of($sum ?? 0.0);
    }

    /**
     * Return an ordered list of movements for the stock card (running balance).
     *
     * @return InventoryStockMovement[]
     */
    public function getLedgerForItem(
        string $itemId,
        ?string $warehouseId = null,
        ?string $locationId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $tenantId = null,
        int $limit = 200,
    ): array {
        return InventoryStockMovement::query()
            ->forTenant($tenantId)
            ->forItem($itemId)
            ->when($warehouseId, fn (Builder $q) => $q->where('warehouse_id', $warehouseId))
            ->when($locationId,  fn (Builder $q) => $q->where('location_id', $locationId))
            ->inDateRange($dateFrom, $dateTo)
            ->orderBy('posted_at')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->all();
    }
}
