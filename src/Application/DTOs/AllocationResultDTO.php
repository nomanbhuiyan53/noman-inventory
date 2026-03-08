<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\DTOs;

use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;

/**
 * Represents a single stock layer resolved by the allocation engine.
 *
 * When issuing or reserving stock, the allocator splits the requested
 * quantity across one or more available layers (batches, locations).
 * Each AllocationResultDTO represents one such layer assignment.
 */
final class AllocationResultDTO
{
    public function __construct(
        public readonly string $itemId,
        public readonly Quantity $allocatedQuantity,
        public readonly string $warehouseId,
        public readonly ?string $locationId = null,
        public readonly ?string $batchId = null,
        public readonly ?string $batchCode = null,
        public readonly ?string $expiryDate = null,
        public readonly ?string $serialCode = null,
    ) {}
}
