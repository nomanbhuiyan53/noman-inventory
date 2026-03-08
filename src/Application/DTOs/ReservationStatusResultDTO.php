<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\DTOs;

final class ReservationStatusResultDTO
{
    public function __construct(
        public readonly string $reservationId,
        public readonly string $itemId,
        public readonly string $itemCode,
        public readonly float $reservedQuantity,
        public readonly string $status,
        public readonly ?string $referenceType,
        public readonly ?string $referenceId,
        public readonly ?string $expiresAt,
        public readonly string $createdAt,
        public readonly ?string $warehouseId = null,
        public readonly ?string $tenantId = null,
    ) {}
}
