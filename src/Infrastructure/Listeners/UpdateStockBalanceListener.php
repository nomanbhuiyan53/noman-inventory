<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Listeners;

use Noman\Inventory\Domain\Inventory\Events\StockAdjusted;
use Noman\Inventory\Domain\Inventory\Events\StockIssued;
use Noman\Inventory\Domain\Inventory\Events\StockReceived;
use Noman\Inventory\Domain\Inventory\Events\StockTransferred;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockDocument;
use Noman\Inventory\Infrastructure\Persistence\Repositories\StockBalanceRepository;

/**
 * Projection listener that updates the inventory_stock_balances table
 * whenever a stock movement is posted.
 *
 * This is the "write side" of the CQRS read model.
 * It keeps the balance projection in sync with the ledger.
 *
 * If config('inventory.queue_projections') is true, this listener
 * is a queued job, decoupled from the posting transaction.
 */
class UpdateStockBalanceListener
{
    public function __construct(
        private readonly StockBalanceRepository $balanceRepository,
    ) {}

    public function handleStockReceived(StockReceived $event): void
    {
        $this->balanceRepository->upsertBalance(
            itemId:        $event->itemId,
            warehouseId:   $event->warehouseId,
            locationId:    $event->locationId,
            batchId:       $event->batchId,
            tenantId:      $event->tenantId,
            quantityDelta: $event->quantity,
        );
    }

    public function handleStockIssued(StockIssued $event): void
    {
        // Reload exact movements from the document to handle multi-allocation splits
        $doc = InventoryStockDocument::with('movements')->find($event->documentId);

        if (! $doc) {
            return;
        }

        foreach ($doc->movements as $movement) {
            $this->balanceRepository->upsertBalance(
                itemId:        $movement->item_id,
                warehouseId:   $movement->warehouse_id,
                locationId:    $movement->location_id,
                batchId:       $movement->batch_id,
                tenantId:      $movement->tenant_id,
                quantityDelta: $movement->quantity, // already negative for outbound
            );
        }
    }

    public function handleStockTransferred(StockTransferred $event): void
    {
        $doc = InventoryStockDocument::with('movements')->find($event->documentId);

        if (! $doc) {
            return;
        }

        foreach ($doc->movements as $movement) {
            $this->balanceRepository->upsertBalance(
                itemId:        $movement->item_id,
                warehouseId:   $movement->warehouse_id,
                locationId:    $movement->location_id,
                batchId:       $movement->batch_id,
                tenantId:      $movement->tenant_id,
                quantityDelta: $movement->quantity,
            );
        }
    }

    public function handleStockAdjusted(StockAdjusted $event): void
    {
        $doc = InventoryStockDocument::with('movements')->find($event->documentId);

        if (! $doc) {
            return;
        }

        foreach ($doc->movements as $movement) {
            $this->balanceRepository->upsertBalance(
                itemId:        $movement->item_id,
                warehouseId:   $movement->warehouse_id,
                locationId:    $movement->location_id,
                batchId:       $movement->batch_id,
                tenantId:      $movement->tenant_id,
                quantityDelta: $movement->quantity,
            );
        }
    }
}
