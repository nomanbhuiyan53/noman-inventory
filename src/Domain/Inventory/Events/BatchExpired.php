<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Inventory\Events;

final class BatchExpired
{
    public function __construct(
        public readonly string $batchId,
        public readonly string $batchCode,
        public readonly string $itemId,
        public readonly float $quantityOnHand,
        public readonly string $expiryDate,
        public readonly ?string $tenantId,
    ) {}
}
