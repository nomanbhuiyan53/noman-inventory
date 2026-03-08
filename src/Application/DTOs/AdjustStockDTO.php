<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\DTOs;

use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;

/**
 * Data Transfer Object for a manual stock adjustment.
 *
 * A positive quantity represents an adjustment-in (stock found/added).
 * A negative quantity represents an adjustment-out (stock lost/removed).
 *
 * Adjustments are typically created from stock count variances or ad-hoc corrections.
 */
final class AdjustStockDTO
{
    /**
     * @param  string               $itemId              Inventory item UUID/ULID
     * @param  Quantity             $quantity            Signed quantity (positive = in, negative = out)
     * @param  string               $warehouseId         Warehouse where adjustment applies
     * @param  string|null          $locationId          Location/bin within the warehouse
     * @param  string|null          $batchCode           Batch reference if applicable
     * @param  string|null          $reason              Reason/justification for the adjustment
     * @param  string|null          $stockCountSessionId Link to the count session that triggered this
     * @param  string|null          $referenceDocNumber  External reference
     * @param  string|null          $notes               Free-text notes
     * @param  string|null          $tenantId            Tenant scope
     * @param  string|null          $idempotencyKey      Prevents duplicate posting on retry
     * @param  array<string,mixed>  $metadata            Additional host-app metadata
     */
    public function __construct(
        public readonly string $itemId,
        public readonly Quantity $quantity,
        public readonly string $warehouseId,
        public readonly ?string $locationId = null,
        public readonly ?string $batchCode = null,
        public readonly ?string $reason = null,
        public readonly ?string $stockCountSessionId = null,
        public readonly ?string $referenceDocNumber = null,
        public readonly ?string $notes = null,
        public readonly ?string $tenantId = null,
        public readonly ?string $idempotencyKey = null,
        public readonly array $metadata = [],
    ) {}
}
