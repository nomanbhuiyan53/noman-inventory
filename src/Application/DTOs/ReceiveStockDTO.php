<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\DTOs;

use Noman\Inventory\Domain\Shared\ValueObjects\Money;
use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;

/**
 * Data Transfer Object for a stock receive operation (inbound stock).
 *
 * Used to receive goods from a purchase, a production run, or a return from customer.
 * Corresponds to the ReceiveStockAction and produces a GRN-type stock document.
 */
final class ReceiveStockDTO
{
    /**
     * @param  string               $itemId             Inventory item UUID/ULID
     * @param  Quantity             $quantity           Quantity to receive
     * @param  string               $warehouseId        Destination warehouse
     * @param  string|null          $locationId         Specific location/bin within the warehouse
     * @param  Money|null           $unitCost           Unit cost of the received goods (for valuation)
     * @param  string|null          $batchCode          Batch/lot code (required if policy dictates)
     * @param  string|null          $expiryDate         Expiry date in Y-m-d format
     * @param  string[]             $serialCodes        Individual serial numbers (for serialised items)
     * @param  string|null          $referenceDocNumber External reference (PO number, invoice number)
     * @param  string|null          $notes              Free-text notes
     * @param  string|null          $tenantId           Tenant scope; null = use TenantResolver
     * @param  string|null          $idempotencyKey     Prevents duplicate posting on retry
     * @param  array<string,mixed>  $metadata           Additional host-app metadata
     */
    public function __construct(
        public readonly string $itemId,
        public readonly Quantity $quantity,
        public readonly string $warehouseId,
        public readonly ?string $locationId = null,
        public readonly ?Money $unitCost = null,
        public readonly ?string $batchCode = null,
        public readonly ?string $expiryDate = null,
        public readonly array $serialCodes = [],
        public readonly ?string $referenceDocNumber = null,
        public readonly ?string $notes = null,
        public readonly ?string $tenantId = null,
        public readonly ?string $idempotencyKey = null,
        public readonly array $metadata = [],
    ) {}
}
