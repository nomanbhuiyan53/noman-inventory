<?php

declare(strict_types=1);

namespace Noman\Inventory\Contracts;

use Noman\Inventory\Application\DTOs\AllocationResultDTO;
use Noman\Inventory\Application\DTOs\StockAllocationRequestDTO;

/**
 * Contract for the stock allocation engine.
 *
 * When issuing, transferring, or reserving stock, the allocator determines
 * WHICH batches/lots/serials/locations to consume and in what order,
 * according to the configured AllocationStrategy (FEFO/FIFO/Manual).
 *
 * The result is a list of AllocationResultDTO objects describing which
 * specific stock layers will be consumed.
 *
 * Host applications can replace the default allocator (FEFO/FIFO) with a
 * custom implementation for complex scenarios (e.g. zone-restricted picking,
 * weight-based allocation, etc.).
 */
interface StockAllocatorContract
{
    /**
     * Allocate stock for the given request.
     *
     * @param  StockAllocationRequestDTO  $request  The allocation request
     * @return AllocationResultDTO[]                 Ordered list of allocation results
     *
     * @throws \Noman\Inventory\Domain\Shared\Exceptions\InsufficientStockException
     */
    public function allocate(StockAllocationRequestDTO $request): array;
}
