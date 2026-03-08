<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\DTOs;

use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;

/**
 * Data Transfer Object for a stock reservation request.
 *
 * Reservations soft-lock a quantity so it cannot be allocated elsewhere.
 * They are released either explicitly (via releaseReservation) or automatically
 * when the reservation_expiry_minutes policy threshold is exceeded.
 */
final class ReserveStockDTO
{
    /**
     * @param  string               $itemId              Inventory item UUID/ULID
     * @param  Quantity             $quantity            Quantity to reserve
     * @param  string               $warehouseId         Warehouse to reserve from
     * @param  string|null          $locationId          Specific location to reserve from
     * @param  string|null          $referenceType       Host app reference type (e.g. 'sales_order')
     * @param  string|null          $referenceId         Host app reference ID
     * @param  int|null             $expiryMinutes       Override reservation expiry; null = use policy
     * @param  string|null          $notes               Free-text notes
     * @param  string|null          $tenantId            Tenant scope
     * @param  array<string,mixed>  $metadata            Additional host-app metadata
     */
    public function __construct(
        public readonly string $itemId,
        public readonly Quantity $quantity,
        public readonly string $warehouseId,
        public readonly ?string $locationId = null,
        public readonly ?string $referenceType = null,
        public readonly ?string $referenceId = null,
        public readonly ?int $expiryMinutes = null,
        public readonly ?string $notes = null,
        public readonly ?string $tenantId = null,
        public readonly array $metadata = [],
    ) {}
}
