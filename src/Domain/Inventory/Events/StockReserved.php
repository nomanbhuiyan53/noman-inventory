<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Inventory\Events;

final class StockReserved
{
    public function __construct(
        public readonly string $reservationId,
        public readonly string $itemId,
        public readonly float $quantity,
        public readonly string $warehouseId,
        public readonly ?string $referenceType,
        public readonly ?string $referenceId,
        public readonly ?string $expiresAt,
        public readonly ?string $tenantId,
    ) {}
}
