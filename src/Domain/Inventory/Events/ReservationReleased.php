<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Inventory\Events;

final class ReservationReleased
{
    public function __construct(
        public readonly string $reservationId,
        public readonly string $itemId,
        public readonly float $quantity,
        public readonly ?string $tenantId,
    ) {}
}
