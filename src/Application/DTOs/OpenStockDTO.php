<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\DTOs;

use Noman\Inventory\Domain\Shared\ValueObjects\Money;
use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;

/**
 * Data Transfer Object for an opening balance stock entry.
 *
 * Used once per item/location during initial setup to establish the
 * starting stock quantity and value. Creates an 'opening' movement type.
 */
final class OpenStockDTO
{
    /**
     * @param  string               $itemId          Inventory item UUID/ULID
     * @param  Quantity             $quantity        Opening quantity
     * @param  string               $warehouseId     Warehouse where the opening balance is set
     * @param  Money|null           $unitCost        Opening unit cost for valuation
     * @param  string|null          $locationId      Specific location/bin
     * @param  string|null          $batchCode       Batch code if applicable
     * @param  string|null          $expiryDate      Expiry date in Y-m-d format
     * @param  string|null          $notes           Free-text notes
     * @param  string|null          $tenantId        Tenant scope
     * @param  array<string,mixed>  $metadata        Additional host-app metadata
     */
    public function __construct(
        public readonly string $itemId,
        public readonly Quantity $quantity,
        public readonly string $warehouseId,
        public readonly ?Money $unitCost = null,
        public readonly ?string $locationId = null,
        public readonly ?string $batchCode = null,
        public readonly ?string $expiryDate = null,
        public readonly ?string $notes = null,
        public readonly ?string $tenantId = null,
        public readonly array $metadata = [],
    ) {}
}
