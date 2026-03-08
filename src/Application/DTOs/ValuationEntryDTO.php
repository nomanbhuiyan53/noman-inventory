<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\DTOs;

use Noman\Inventory\Domain\Shared\Enums\MovementType;
use Noman\Inventory\Domain\Shared\Enums\ValuationMethod;
use Noman\Inventory\Domain\Shared\ValueObjects\Money;
use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;

/**
 * DTO representing a created valuation entry record.
 */
final class ValuationEntryDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $documentLineId,
        public readonly string $itemId,
        public readonly Quantity $quantity,
        public readonly Money $unitCost,
        public readonly Money $totalCost,
        public readonly MovementType $movementType,
        public readonly ValuationMethod $valuationMethod,
        public readonly ?string $batchId,
        public readonly ?string $tenantId,
    ) {}
}
