<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\DTOs;

final class BatchExpiryResultDTO
{
    public function __construct(
        public readonly string $batchId,
        public readonly string $batchCode,
        public readonly string $itemId,
        public readonly string $itemCode,
        public readonly string $itemName,
        public readonly string $expiryDate,
        public readonly int $daysUntilExpiry,
        public readonly float $quantityOnHand,
        public readonly bool $isExpired,
        public readonly ?string $warehouseId = null,
        public readonly ?string $tenantId = null,
    ) {}
}
