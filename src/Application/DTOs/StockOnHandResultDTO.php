<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\DTOs;

/**
 * A single row in the Stock On Hand report.
 */
final class StockOnHandResultDTO
{
    public function __construct(
        public readonly string $itemId,
        public readonly string $itemCode,
        public readonly string $itemName,
        public readonly float $quantityOnHand,
        public readonly float $quantityReserved,
        public readonly float $quantityAvailable,
        public readonly string $unitCode,
        public readonly ?string $warehouseId = null,
        public readonly ?string $warehouseName = null,
        public readonly ?string $locationId = null,
        public readonly ?string $locationCode = null,
        public readonly ?string $tenantId = null,
    ) {}
}
