<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Repositories;

use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockBalance;

/**
 * Repository for reading and updating the stock balance projection table.
 *
 * This is the fast-path for balance reads. Actions should use this instead of
 * the ledger aggregation for real-time available quantity checks.
 *
 * The projection is maintained by listeners (Phase 5) and must not be written
 * to directly from Actions outside of the projection update path.
 */
class StockBalanceRepository
{
    public function getBalance(
        string $itemId,
        string $warehouseId,
        ?string $locationId = null,
        ?string $batchId = null,
        ?string $tenantId = null,
    ): ?InventoryStockBalance {
        return InventoryStockBalance::query()
            ->forTenant($tenantId)
            ->forItem($itemId)
            ->forWarehouse($warehouseId)
            ->where('location_id', $locationId)
            ->where('batch_id', $batchId)
            ->first();
    }

    /**
     * Get the total available quantity across all locations for an item in a warehouse.
     */
    public function getTotalAvailableQuantity(
        string $itemId,
        string $warehouseId,
        ?string $tenantId = null,
    ): Quantity {
        $total = InventoryStockBalance::query()
            ->forTenant($tenantId)
            ->forItem($itemId)
            ->forWarehouse($warehouseId)
            ->sum('quantity_available');

        return Quantity::of($total ?? 0.0);
    }

    /**
     * Get all balance rows for an item across all warehouses.
     *
     * @return InventoryStockBalance[]
     */
    public function getAllForItem(string $itemId, ?string $tenantId = null): array
    {
        return InventoryStockBalance::query()
            ->forTenant($tenantId)
            ->forItem($itemId)
            ->get()
            ->all();
    }

    /**
     * Upsert a balance row for the given dimension key.
     * Called by projection listeners after a stock movement is posted.
     */
    public function upsertBalance(
        string $itemId,
        string $warehouseId,
        ?string $locationId,
        ?string $batchId,
        ?string $tenantId,
        float $quantityDelta,
        float $reservedDelta = 0.0,
    ): void {
        $balance = InventoryStockBalance::firstOrNew([
            'tenant_id'    => $tenantId,
            'item_id'      => $itemId,
            'warehouse_id' => $warehouseId,
            'location_id'  => $locationId,
            'batch_id'     => $batchId,
        ]);

        $balance->quantity_on_hand   = ($balance->quantity_on_hand   ?? 0.0) + $quantityDelta;
        $balance->quantity_reserved  = ($balance->quantity_reserved  ?? 0.0) + $reservedDelta;
        $balance->quantity_available = $balance->quantity_on_hand - $balance->quantity_reserved;
        $balance->currency           = config('inventory.currency', 'USD');
        $balance->last_movement_at   = now();

        $balance->save();
    }

    /**
     * Increment the reserved quantity (called when a reservation is created).
     */
    public function incrementReserved(
        string $itemId,
        string $warehouseId,
        ?string $locationId,
        ?string $tenantId,
        float $quantity,
    ): void {
        $this->upsertBalance(
            itemId:       $itemId,
            warehouseId:  $warehouseId,
            locationId:   $locationId,
            batchId:      null,
            tenantId:     $tenantId,
            quantityDelta:0.0,
            reservedDelta:$quantity,
        );
    }

    /**
     * Decrement the reserved quantity (called when a reservation is released/consumed).
     */
    public function decrementReserved(
        string $itemId,
        string $warehouseId,
        ?string $locationId,
        ?string $tenantId,
        float $quantity,
    ): void {
        $this->upsertBalance(
            itemId:       $itemId,
            warehouseId:  $warehouseId,
            locationId:   $locationId,
            batchId:      null,
            tenantId:     $tenantId,
            quantityDelta:0.0,
            reservedDelta:-$quantity,
        );
    }
}
