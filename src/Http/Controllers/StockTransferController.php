<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Noman\Inventory\Application\DTOs\TransferStockDTO;
use Noman\Inventory\Contracts\InventoryManagerContract;
use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;
use Noman\Inventory\Http\Requests\TransferStockRequest;
use Noman\Inventory\Http\Resources\StockDocumentResultResource;

class StockTransferController extends Controller
{
    public function __construct(
        private readonly InventoryManagerContract $inventory,
    ) {}

    public function store(TransferStockRequest $request): JsonResponse
    {
        $data = $request->validated();

        $dto = new TransferStockDTO(
            itemId:             $data['item_id'],
            quantity:           Quantity::of($data['quantity']),
            fromWarehouseId:    $data['from_warehouse_id'],
            toWarehouseId:      $data['to_warehouse_id'],
            fromLocationId:     $data['from_location_id'] ?? null,
            toLocationId:       $data['to_location_id'] ?? null,
            batchCode:          $data['batch_code'] ?? null,
            serialCodes:        $data['serial_codes'] ?? [],
            referenceDocNumber: $data['reference_doc_number'] ?? null,
            notes:              $data['notes'] ?? null,
            idempotencyKey:     $data['idempotency_key'] ?? null,
            metadata:           $data['metadata'] ?? [],
        );

        $result = $this->inventory->transfer($dto);

        return response()->json(
            (new StockDocumentResultResource($result))->toArray($request),
            201
        );
    }
}
