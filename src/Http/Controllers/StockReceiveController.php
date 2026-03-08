<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Noman\Inventory\Application\DTOs\ReceiveStockDTO;
use Noman\Inventory\Contracts\InventoryManagerContract;
use Noman\Inventory\Domain\Shared\ValueObjects\Money;
use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;
use Noman\Inventory\Http\Requests\ReceiveStockRequest;
use Noman\Inventory\Http\Resources\StockDocumentResultResource;

class StockReceiveController extends Controller
{
    public function __construct(
        private readonly InventoryManagerContract $inventory,
    ) {}

    public function store(ReceiveStockRequest $request): JsonResponse
    {
        $data = $request->validated();

        $unitCost = isset($data['unit_cost'])
            ? Money::of($data['unit_cost'], $data['currency'] ?? config('inventory.currency', 'USD'))
            : null;

        $dto = new ReceiveStockDTO(
            itemId:              $data['item_id'],
            quantity:            Quantity::of($data['quantity']),
            warehouseId:         $data['warehouse_id'],
            locationId:          $data['location_id'] ?? null,
            unitCost:            $unitCost,
            batchCode:           $data['batch_code'] ?? null,
            expiryDate:          $data['expiry_date'] ?? null,
            serialCodes:         $data['serial_codes'] ?? [],
            referenceDocNumber:  $data['reference_doc_number'] ?? null,
            notes:               $data['notes'] ?? null,
            idempotencyKey:      $data['idempotency_key'] ?? null,
            metadata:            $data['metadata'] ?? [],
        );

        $result = $this->inventory->receive($dto);

        return response()->json(
            (new StockDocumentResultResource($result))->toArray($request),
            201
        );
    }
}
