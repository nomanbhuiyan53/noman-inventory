<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\DTOs;

use Noman\Inventory\Domain\Shared\Enums\MovementType;
use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;

/**
 * Data Transfer Object for a stock issue operation (outbound stock).
 *
 * Covers sales, returns to vendor, wastage, dead stock write-offs, etc.
 * The specific outbound purpose is captured via the MovementType.
 */
final class IssueStockDTO
{
    /**
     * @param  string               $itemId              Inventory item UUID/ULID
     * @param  Quantity             $quantity            Quantity to issue
     * @param  string               $warehouseId         Source warehouse
     * @param  MovementType         $movementType        The type of outbound movement
     * @param  string|null          $locationId          Specific location/bin
     * @param  string|null          $batchCode           Explicit batch to issue from (Manual strategy)
     * @param  string[]             $serialCodes         Explicit serial numbers (Manual strategy)
     * @param  string|null          $referenceDocNumber  External reference (Sales Order, etc.)
     * @param  string|null          $notes               Free-text notes
     * @param  string|null          $tenantId            Tenant scope
     * @param  string|null          $idempotencyKey      Prevents duplicate posting on retry
     * @param  array<string,mixed>  $metadata            Additional host-app metadata
     */
    public function __construct(
        public readonly string $itemId,
        public readonly Quantity $quantity,
        public readonly string $warehouseId,
        public readonly MovementType $movementType = MovementType::SaleOut,
        public readonly ?string $locationId = null,
        public readonly ?string $batchCode = null,
        public readonly array $serialCodes = [],
        public readonly ?string $referenceDocNumber = null,
        public readonly ?string $notes = null,
        public readonly ?string $tenantId = null,
        public readonly ?string $idempotencyKey = null,
        public readonly array $metadata = [],
    ) {}
}
