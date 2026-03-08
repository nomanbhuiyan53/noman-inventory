<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\Queries;

final class ValuationSummaryQuery
{
    public function __construct(
        public readonly ?string $itemId = null,
        public readonly ?string $warehouseId = null,
        public readonly ?string $dateAsOf = null,
        public readonly ?string $tenantId = null,
    ) {}
}
