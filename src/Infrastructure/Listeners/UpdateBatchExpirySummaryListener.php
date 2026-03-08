<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Listeners;

use Illuminate\Support\Str;
use Noman\Inventory\Domain\Inventory\Events\StockReceived;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryBatch;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryBatchExpirySummary;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockBalance;

/**
 * Updates the inventory_batch_expiry_summary projection table whenever
 * stock is received or adjusted. This enables fast batch expiry reports.
 */
class UpdateBatchExpirySummaryListener
{
    public function handleStockReceived(StockReceived $event): void
    {
        if (! $event->batchId) {
            return;
        }

        $this->refreshSummaryForBatch($event->batchId, $event->warehouseId, $event->tenantId);
    }

    private function refreshSummaryForBatch(
        string $batchId,
        ?string $warehouseId,
        ?string $tenantId,
    ): void {
        $batch = InventoryBatch::find($batchId);

        if (! $batch || ! $batch->expiry_date) {
            return;
        }

        // Sum on-hand quantity for this batch from balance projection
        $qtyOnHand = InventoryStockBalance::query()
            ->forTenant($tenantId)
            ->where('batch_id', $batchId)
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->sum('quantity_on_hand');

        $now          = now();
        $expiryDate   = $batch->expiry_date;
        $daysUntil    = (int) $now->diffInDays($expiryDate, false);
        $isExpired    = $expiryDate->isPast();

        InventoryBatchExpirySummary::updateOrCreate(
            [
                'batch_id'     => $batchId,
                'warehouse_id' => $warehouseId,
            ],
            [
                'id'               => (string) Str::ulid(),
                'tenant_id'        => $tenantId,
                'item_id'          => $batch->item_id,
                'quantity_on_hand' => $qtyOnHand,
                'expiry_date'      => $expiryDate->toDateString(),
                'days_until_expiry'=> $daysUntil,
                'is_expired'       => $isExpired,
            ]
        );
    }
}
