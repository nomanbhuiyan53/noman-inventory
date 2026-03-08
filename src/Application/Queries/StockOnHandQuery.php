<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\Queries;

/**
 * Query parameters for the Stock On Hand report.
 */
final class StockOnHandQuery
{
    public function __construct(
        public readonly ?string $itemId = null,
        public readonly ?string $itemTypeId = null,
        public readonly ?string $categoryId = null,
        public readonly ?string $warehouseId = null,
        public readonly ?string $locationId = null,
        public readonly ?string $tenantId = null,
        public readonly bool $includeZeroBalance = false,
        public readonly bool $includeNegativeBalance = false,
    ) {}
}
