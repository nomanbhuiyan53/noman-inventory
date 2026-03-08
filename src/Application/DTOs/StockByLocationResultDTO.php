<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\DTOs;

final class StockByLocationResultDTO
{
    public function __construct(
        public readonly string $itemId,
        public readonly string $itemCode,
        public readonly string $warehouseId,
        public readonly string $warehouseName,
        public readonly ?string $locationId,
        public readonly ?string $locationCode,
        public readonly float $quantity,
        public readonly float $quantityReserved,
        public readonly ?string $tenantId = null,
    ) {}
}
