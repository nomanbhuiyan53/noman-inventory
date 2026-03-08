<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Noman\Inventory\Application\DTOs\ReserveStockDTO;
use Noman\Inventory\Contracts\InventoryManagerContract;
use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;
use Noman\Inventory\Http\Requests\ReserveStockRequest;

class ReservationController extends Controller
{
    public function __construct(
        private readonly InventoryManagerContract $inventory,
    ) {}

    public function store(ReserveStockRequest $request): JsonResponse
    {
        $data = $request->validated();

        $dto = new ReserveStockDTO(
            itemId:        $data['item_id'],
            quantity:      Quantity::of($data['quantity']),
            warehouseId:   $data['warehouse_id'],
            locationId:    $data['location_id'] ?? null,
            referenceType: $data['reference_type'] ?? null,
            referenceId:   $data['reference_id'] ?? null,
            expiryMinutes: isset($data['expiry_minutes']) ? (int) $data['expiry_minutes'] : null,
            notes:         $data['notes'] ?? null,
            metadata:      $data['metadata'] ?? [],
        );

        $reservationId = $this->inventory->reserve($dto);

        return response()->json(['reservation_id' => $reservationId], 201);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->inventory->releaseReservation($id);

        return response()->json(['message' => 'Reservation released.']);
    }
}
