<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Inventory\Events;

/**
 * Fired after a stock receipt document is posted to the ledger.
 */
final class StockReceived
{
    public function __construct(
        public readonly string $documentId,
        public readonly string $documentNumber,
        public readonly string $itemId,
        public readonly float $quantity,
        public readonly string $warehouseId,
        public readonly ?string $locationId,
        public readonly ?string $batchId,
        public readonly ?string $tenantId,
    ) {}
}
