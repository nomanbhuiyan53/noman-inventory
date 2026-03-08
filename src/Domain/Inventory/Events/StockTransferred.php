<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Inventory\Events;

final class StockTransferred
{
    public function __construct(
        public readonly string $documentId,
        public readonly string $documentNumber,
        public readonly string $itemId,
        public readonly float $quantity,
        public readonly string $fromWarehouseId,
        public readonly string $toWarehouseId,
        public readonly ?string $tenantId,
    ) {}
}
