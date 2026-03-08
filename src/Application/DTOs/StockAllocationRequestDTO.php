<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\DTOs;

use Noman\Inventory\Domain\Shared\Enums\AllocationStrategy;
use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;

/**
 * Input request passed to the StockAllocatorContract.
 *
 * Describes what needs to be allocated, from where, and which strategy to use.
 */
final class StockAllocationRequestDTO
{
    /**
     * @param  string               $itemId              Inventory item to allocate
     * @param  Quantity             $quantity            Quantity required
     * @param  string               $warehouseId         Warehouse to allocate from
     * @param  AllocationStrategy   $strategy            FEFO / FIFO / Manual
     * @param  string|null          $locationId          Restrict to a specific location
     * @param  string|null          $batchCode           Explicit batch code (Manual only)
     * @param  string[]             $serialCodes         Explicit serial numbers (Manual only)
     * @param  bool                 $excludeExpired      Exclude expired batches from allocation
     * @param  bool                 $excludeReserved     Exclude already reserved quantities
     * @param  string|null          $tenantId            Tenant scope
     */
    public function __construct(
        public readonly string $itemId,
        public readonly Quantity $quantity,
        public readonly string $warehouseId,
        public readonly AllocationStrategy $strategy = AllocationStrategy::Fefo,
        public readonly ?string $locationId = null,
        public readonly ?string $batchCode = null,
        public readonly array $serialCodes = [],
        public readonly bool $excludeExpired = true,
        public readonly bool $excludeReserved = true,
        public readonly ?string $tenantId = null,
    ) {}
}
