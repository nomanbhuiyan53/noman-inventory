<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Repositories;

use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryReservation;

/**
 * Repository for stock reservation management.
 */
class ReservationRepository
{
    public function findById(string $reservationId, ?string $tenantId = null): ?InventoryReservation
    {
        return InventoryReservation::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->find($reservationId);
    }

    /**
     * Sum of active reservations for an item in a warehouse.
     */
    public function getTotalReservedQuantity(
        string $itemId,
        string $warehouseId,
        ?string $locationId = null,
        ?string $tenantId = null,
    ): float {
        return (float) InventoryReservation::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
            ->active()
            ->sum('quantity');
    }

    /**
     * @return InventoryReservation[]
     */
    public function getExpiredReservations(?string $tenantId = null): array
    {
        return InventoryReservation::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->expired()
            ->get()
            ->all();
    }

    public function save(InventoryReservation $reservation): InventoryReservation
    {
        $reservation->save();

        return $reservation;
    }
}
