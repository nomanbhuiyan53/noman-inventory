<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Inventory\Events;

final class StockAdjusted
{
    public function __construct(
        public readonly string $documentId,
        public readonly string $documentNumber,
        public readonly string $itemId,
        public readonly float $quantity,
        public readonly string $warehouseId,
        public readonly string $reason,
        public readonly ?string $tenantId,
    ) {}
}
