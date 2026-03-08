<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\Actions;

use Noman\Inventory\Contracts\TenantResolverContract;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryReservation;
use Noman\Inventory\Infrastructure\Persistence\Repositories\StockBalanceRepository;

/**
 * Releases a previously created stock reservation.
 *
 * Returns the reserved quantity back to available stock in the balance projection.
 * The reservation record is updated to status='released', not deleted.
 */
final class ReleaseReservationAction
{
    public function __construct(
        private readonly TenantResolverContract $tenantResolver,
    ) {}

    /**
     * @throws \Noman\Inventory\Domain\Shared\Exceptions\InventoryException
     */
    public function execute(string $reservationId): void
    {
        $tenantId    = $this->tenantResolver->getCurrentTenantId()?->getValue();
        $reservation = InventoryReservation::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->findOrFail($reservationId);

        if (! $reservation->isActive()) {
            // Already released/expired/consumed — idempotent no-op
            return;
        }

        $reservation->status      = 'released';
        $reservation->released_at = now();
        $reservation->save();

        // Decrement reserved quantity in balance projection
        (new StockBalanceRepository())->decrementReserved(
            itemId:      $reservation->item_id,
            warehouseId: $reservation->warehouse_id,
            locationId:  $reservation->location_id,
            tenantId:    $tenantId,
            quantity:    $reservation->quantity,
        );

        event(new \Noman\Inventory\Domain\Inventory\Events\ReservationReleased(
            reservationId: $reservation->id,
            itemId:        $reservation->item_id,
            quantity:      $reservation->quantity,
            tenantId:      $tenantId,
        ));
    }
}
