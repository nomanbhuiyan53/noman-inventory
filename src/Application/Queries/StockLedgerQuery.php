<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\Queries;

use Noman\Inventory\Domain\Shared\Enums\MovementType;

final class StockLedgerQuery
{
    /**
     * @param  MovementType[]  $movementTypes  Filter by specific movement types
     */
    public function __construct(
        public readonly ?string $itemId = null,
        public readonly ?string $warehouseId = null,
        public readonly ?string $locationId = null,
        public readonly ?string $batchId = null,
        public readonly array $movementTypes = [],
        public readonly ?string $dateFrom = null,
        public readonly ?string $dateTo = null,
        public readonly ?string $tenantId = null,
        public readonly int $perPage = 50,
        public readonly int $page = 1,
    ) {}
}
