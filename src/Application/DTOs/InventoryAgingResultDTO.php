<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\DTOs;

final class InventoryAgingResultDTO
{
    public function __construct(
        public readonly string $itemId,
        public readonly string $itemCode,
        public readonly string $itemName,
        public readonly float $qty0To30Days,
        public readonly float $qty31To60Days,
        public readonly float $qty61To90Days,
        public readonly float $qtyOver90Days,
        public readonly float $totalQuantity,
        public readonly ?string $warehouseId = null,
        public readonly ?string $tenantId = null,
    ) {}
}
