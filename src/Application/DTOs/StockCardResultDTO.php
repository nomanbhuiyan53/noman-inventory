<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\DTOs;

final class StockCardResultDTO
{
    public function __construct(
        public readonly string $movementId,
        public readonly string $movementType,
        public readonly string $movementTypeLabel,
        public readonly float $inQuantity,
        public readonly float $outQuantity,
        public readonly float $balance,
        public readonly string $documentNumber,
        public readonly string $date,
        public readonly ?string $batchCode = null,
        public readonly ?string $notes = null,
    ) {}
}
