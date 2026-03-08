<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\DTOs;

use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;

/**
 * Data Transfer Object for a stock transfer operation.
 *
 * Moves stock from one warehouse/location to another.
 * Creates a paired TransferOut (source) and TransferIn (destination) document.
 */
final class TransferStockDTO
{
    /**
     * @param  string               $itemId                  Inventory item UUID/ULID
     * @param  Quantity             $quantity                Quantity to transfer
     * @param  string               $fromWarehouseId         Source warehouse
     * @param  string               $toWarehouseId           Destination warehouse
     * @param  string|null          $fromLocationId          Source location/bin
     * @param  string|null          $toLocationId            Destination location/bin
     * @param  string|null          $batchCode               Batch to transfer (Manual strategy)
     * @param  string[]             $serialCodes             Serial numbers to transfer
     * @param  string|null          $referenceDocNumber      External reference
     * @param  string|null          $notes                   Free-text notes
     * @param  string|null          $tenantId                Tenant scope
     * @param  string|null          $idempotencyKey          Prevents duplicate posting on retry
     * @param  array<string,mixed>  $metadata                Additional host-app metadata
     */
    public function __construct(
        public readonly string $itemId,
        public readonly Quantity $quantity,
        public readonly string $fromWarehouseId,
        public readonly string $toWarehouseId,
        public readonly ?string $fromLocationId = null,
        public readonly ?string $toLocationId = null,
        public readonly ?string $batchCode = null,
        public readonly array $serialCodes = [],
        public readonly ?string $referenceDocNumber = null,
        public readonly ?string $notes = null,
        public readonly ?string $tenantId = null,
        public readonly ?string $idempotencyKey = null,
        public readonly array $metadata = [],
    ) {}
}
