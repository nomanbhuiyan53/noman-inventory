<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\Queries;

final class ReservationStatusQuery
{
    public function __construct(
        public readonly ?string $itemId = null,
        public readonly ?string $referenceType = null,
        public readonly ?string $referenceId = null,
        public readonly bool $activeOnly = true,
        public readonly ?string $tenantId = null,
    ) {}
}
