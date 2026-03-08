<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Allocation;

use Noman\Inventory\Application\DTOs\AllocationResultDTO;
use Noman\Inventory\Application\DTOs\StockAllocationRequestDTO;
use Noman\Inventory\Contracts\StockAllocatorContract;
use Noman\Inventory\Domain\Shared\Enums\AllocationStrategy;
use Noman\Inventory\Domain\Shared\Exceptions\InsufficientStockException;
use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;

/**
 * FEFO / FIFO stock allocator.
 *
 * Implements both FEFO (First Expired, First Out) and FIFO (First In, First Out)
 * strategies by ordering available stock layers from the stock_balances or
 * stock_movements tables, then greedily consuming them until the requested
 * quantity is fulfilled.
 *
 * Phase 4 will flesh out the database query that retrieves available stock layers
 * with expiry-date or receipt-date ordering depending on the strategy.
 */
final class FefoAllocator implements StockAllocatorContract
{
    /**
     * @return AllocationResultDTO[]
     *
     * @throws InsufficientStockException
     */
    public function allocate(StockAllocationRequestDTO $request): array
    {
        if ($request->strategy === AllocationStrategy::Manual) {
            return $this->allocateManual($request);
        }

        return $this->allocateAutomatic($request);
    }

    /**
     * Automatic FEFO/FIFO allocation.
     * Queries available layers ordered by expiry date (FEFO) or receipt date (FIFO).
     *
     * TODO (Phase 4): Replace stub with real DB-backed layer query.
     */
    private function allocateAutomatic(StockAllocationRequestDTO $request): array
    {
        // TODO: Query inventory_stock_balances / inventory_batches ordered by
        //   - expiry_date ASC (FEFO) or created_at ASC (FIFO)
        // Filter by:
        //   - item_id = $request->itemId
        //   - warehouse_id = $request->warehouseId
        //   - location_id = $request->locationId (if set)
        //   - exclude expired if $request->excludeExpired
        //   - quantity > reserved_quantity if $request->excludeReserved
        //   - tenant_id = $request->tenantId (if set)
        //
        // Then greedily fill allocation buckets until requested quantity is met.
        // Throw InsufficientStockException if layers cannot cover the requested qty.

        return [];
    }

    /**
     * Manual allocation where the caller explicitly specifies batch and serials.
     * Validates that the specified items exist and have enough available stock.
     *
     * TODO (Phase 4): Validate explicit batch/serial stock availability from DB.
     */
    private function allocateManual(StockAllocationRequestDTO $request): array
    {
        // TODO: Validate that the specified batchCode and serialCodes exist
        // in the inventory and have sufficient available quantity.

        $results = [];

        if ($request->batchCode) {
            $results[] = new AllocationResultDTO(
                itemId:             $request->itemId,
                allocatedQuantity:  $request->quantity,
                warehouseId:        $request->warehouseId,
                locationId:         $request->locationId,
                batchCode:          $request->batchCode,
            );
        }

        return $results;
    }
}
