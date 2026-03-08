<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Noman\Inventory\Application\DTOs\AdjustStockDTO;
use Noman\Inventory\Contracts\InventoryManagerContract;
use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;
use Noman\Inventory\Http\Requests\AdjustStockRequest;
use Noman\Inventory\Http\Resources\StockDocumentResultResource;

class StockAdjustmentController extends Controller
{
    public function __construct(
        private readonly InventoryManagerContract $inventory,
    ) {}

    public function store(AdjustStockRequest $request): JsonResponse
    {
        $data = $request->validated();

        $dto = new AdjustStockDTO(
            itemId:             $data['item_id'],
            quantity:           Quantity::of($data['quantity']),
            warehouseId:        $data['warehouse_id'],
            locationId:         $data['location_id'] ?? null,
            batchCode:          $data['batch_code'] ?? null,
            reason:             $data['reason'],
            referenceDocNumber: $data['reference_doc_number'] ?? null,
            notes:              $data['notes'] ?? null,
            idempotencyKey:     $data['idempotency_key'] ?? null,
            metadata:           $data['metadata'] ?? [],
        );

        $result = $this->inventory->adjust($dto);

        return response()->json(
            (new StockDocumentResultResource($result))->toArray($request),
            201
        );
    }
}
