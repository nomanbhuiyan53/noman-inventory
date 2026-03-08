<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Inventory\Events;

final class StockCountStarted
{
    public function __construct(
        public readonly string $sessionId,
        public readonly string $warehouseId,
        public readonly ?string $tenantId,
    ) {}
}
