<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\Queries;

final class StockCardQuery
{
    public function __construct(
        public readonly string $itemId,
        public readonly ?string $warehouseId = null,
        public readonly ?string $locationId = null,
        public readonly ?string $dateFrom = null,
        public readonly ?string $dateTo = null,
        public readonly ?string $tenantId = null,
    ) {}
}
