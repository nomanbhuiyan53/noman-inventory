<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\Actions;

use Illuminate\Support\Str;
use Noman\Inventory\Application\DTOs\ReserveStockDTO;
use Noman\Inventory\Contracts\PolicyResolverContract;
use Noman\Inventory\Contracts\TenantResolverContract;
use Noman\Inventory\Domain\Shared\Exceptions\InsufficientStockException;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryReservation;
use Noman\Inventory\Infrastructure\Persistence\Repositories\StockBalanceRepository;

/**
 * Creates a stock reservation (soft-lock) to prevent over-selling.
 *
 * Returns the reservation ID. The caller should store this ID
 * to be able to release the reservation later via ReleaseReservationAction.
 */
final class ReserveStockAction
{
    public function __construct(
        private readonly TenantResolverContract $tenantResolver,
        private readonly PolicyResolverContract $policyResolver,
    ) {}

    /**
     * @throws InsufficientStockException
     * @return string  The new reservation ID
     */
    public function execute(ReserveStockDTO $dto): string
    {
        $tenantId = $dto->tenantId ?? $this->tenantResolver->getCurrentTenantId()?->getValue();
        $policy   = $this->policyResolver->resolveForItem($dto->itemId);

        // Check available stock
        $balanceRepo = new StockBalanceRepository();
        $available   = $balanceRepo->getTotalAvailableQuantity($dto->itemId, $dto->warehouseId, $tenantId);

        if ($dto->quantity->greaterThan($available)) {
            throw InsufficientStockException::forItem($dto->itemId, $dto->quantity, $available);
        }

        // Determine expiry time
        $expiryMinutes = $dto->expiryMinutes
            ?? $policy->reservationExpiryMinutes
            ?? config('inventory.reservation_expiry_minutes');

        $expiresAt = $expiryMinutes ? now()->addMinutes($expiryMinutes) : null;

        $reservation = InventoryReservation::create([
            'id'             => (string) Str::ulid(),
            'tenant_id'      => $tenantId,
            'item_id'        => $dto->itemId,
            'warehouse_id'   => $dto->warehouseId,
            'location_id'    => $dto->locationId,
            'quantity'       => $dto->quantity->getValue(),
            'status'         => 'active',
            'reference_type' => $dto->referenceType,
            'reference_id'   => $dto->referenceId,
            'expires_at'     => $expiresAt,
            'notes'          => $dto->notes,
            'metadata'       => $dto->metadata ?: null,
        ]);

        // Update the reserved quantity in the balance projection
        $balanceRepo->incrementReserved(
            itemId:      $dto->itemId,
            warehouseId: $dto->warehouseId,
            locationId:  $dto->locationId,
            tenantId:    $tenantId,
            quantity:    $dto->quantity->getValue(),
        );

        event(new \Noman\Inventory\Domain\Inventory\Events\StockReserved(
            reservationId: $reservation->id,
            itemId:        $dto->itemId,
            quantity:      $dto->quantity->getValue(),
            warehouseId:   $dto->warehouseId,
            referenceType: $dto->referenceType,
            referenceId:   $dto->referenceId,
            expiresAt:     $expiresAt?->toDateTimeString(),
            tenantId:      $tenantId,
        ));

        return $reservation->id;
    }
}
