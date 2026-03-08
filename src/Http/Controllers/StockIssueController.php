<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Noman\Inventory\Application\DTOs\IssueStockDTO;
use Noman\Inventory\Contracts\InventoryManagerContract;
use Noman\Inventory\Domain\Shared\Enums\MovementType;
use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;
use Noman\Inventory\Http\Requests\IssueStockRequest;
use Noman\Inventory\Http\Resources\StockDocumentResultResource;

class StockIssueController extends Controller
{
    public function __construct(
        private readonly InventoryManagerContract $inventory,
    ) {}

    public function store(IssueStockRequest $request): JsonResponse
    {
        $data = $request->validated();

        $dto = new IssueStockDTO(
            itemId:             $data['item_id'],
            quantity:           Quantity::of($data['quantity']),
            warehouseId:        $data['warehouse_id'],
            movementType:       MovementType::from($data['movement_type']),
            locationId:         $data['location_id'] ?? null,
            batchCode:          $data['batch_code'] ?? null,
            serialCodes:        $data['serial_codes'] ?? [],
            referenceDocNumber: $data['reference_doc_number'] ?? null,
            notes:              $data['notes'] ?? null,
            idempotencyKey:     $data['idempotency_key'] ?? null,
            metadata:           $data['metadata'] ?? [],
        );

        $result = $this->inventory->issue($dto);

        return response()->json(
            (new StockDocumentResultResource($result))->toArray($request),
            201
        );
    }
}
