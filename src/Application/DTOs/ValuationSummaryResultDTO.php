<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\DTOs;

final class ValuationSummaryResultDTO
{
    public function __construct(
        public readonly string $itemId,
        public readonly string $itemCode,
        public readonly string $itemName,
        public readonly float $quantityOnHand,
        public readonly float $averageUnitCost,
        public readonly float $totalValue,
        public readonly string $currency,
        public readonly string $valuationMethod,
        public readonly ?string $warehouseId = null,
        public readonly ?string $tenantId = null,
    ) {}
}
