<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\Queries;

final class BatchExpiryQuery
{
    public function __construct(
        public readonly ?string $itemId = null,
        public readonly ?string $warehouseId = null,
        public readonly ?int $expiringWithinDays = 30,
        public readonly bool $includeExpired = false,
        public readonly ?string $tenantId = null,
    ) {}
}
