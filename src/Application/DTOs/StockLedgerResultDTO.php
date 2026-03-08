<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\DTOs;

final class StockLedgerResultDTO
{
    public function __construct(
        public readonly string $movementId,
        public readonly string $itemId,
        public readonly string $itemCode,
        public readonly string $movementType,
        public readonly float $quantity,
        public readonly float $runningBalance,
        public readonly string $documentNumber,
        public readonly string $postedAt,
        public readonly ?string $warehouseId = null,
        public readonly ?string $locationId = null,
        public readonly ?string $batchCode = null,
        public readonly ?string $tenantId = null,
    ) {}
}
