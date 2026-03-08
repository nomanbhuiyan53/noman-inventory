<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Repositories;

use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryBatch;

/**
 * Repository for batch/lot record management.
 */
class BatchRepository
{
    public function findById(string $batchId, ?string $tenantId = null): ?InventoryBatch
    {
        return InventoryBatch::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->find($batchId);
    }

    public function findByCode(
        string $itemId,
        string $batchCode,
        ?string $tenantId = null,
    ): ?InventoryBatch {
        return InventoryBatch::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('item_id', $itemId)
            ->where('batch_code', $batchCode)
            ->first();
    }

    /**
     * Find or create a batch record when receiving stock.
     */
    public function firstOrCreateBatch(
        string $itemId,
        string $batchCode,
        array $attributes,
        ?string $tenantId = null,
    ): InventoryBatch {
        return InventoryBatch::firstOrCreate(
            [
                'tenant_id'  => $tenantId,
                'item_id'    => $itemId,
                'batch_code' => $batchCode,
            ],
            $attributes
        );
    }

    /**
     * Returns batches ordered by FEFO (expiry date ASC, nulls last).
     *
     * @return InventoryBatch[]
     */
    public function getFefoOrderedBatches(string $itemId, ?string $tenantId = null): array
    {
        return InventoryBatch::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('item_id', $itemId)
            ->notExpired()
            ->orderByFefo()
            ->get()
            ->all();
    }

    /**
     * Returns batches ordered by FIFO (created_at ASC).
     *
     * @return InventoryBatch[]
     */
    public function getFifoOrderedBatches(string $itemId, ?string $tenantId = null): array
    {
        return InventoryBatch::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('item_id', $itemId)
            ->notExpired()
            ->orderByFifo()
            ->get()
            ->all();
    }

    public function save(InventoryBatch $batch): InventoryBatch
    {
        $batch->save();

        return $batch;
    }
}
