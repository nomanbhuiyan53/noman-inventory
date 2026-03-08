<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Noman\Inventory\Application\Actions\CompleteStockCountAction;
use Noman\Inventory\Application\Actions\StartStockCountAction;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockCountSession;

class StockCountController extends Controller
{
    public function __construct(
        private readonly StartStockCountAction $startAction,
        private readonly CompleteStockCountAction $completeAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $sessions = InventoryStockCountSession::query()
            ->when($request->get('tenant_id'), fn ($q) => $q->where('tenant_id', $request->get('tenant_id')))
            ->when($request->get('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->get('warehouse_id')))
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 20));

        return response()->json($sessions);
    }

    public function show(string $id): JsonResponse
    {
        return response()->json(
            InventoryStockCountSession::with(['warehouse', 'entries', 'entries.item'])->findOrFail($id)
        );
    }

    public function start(Request $request): JsonResponse
    {
        $data = $request->validate([
            'warehouse_id' => ['required', 'string'],
            'location_id'  => ['nullable', 'string'],
            'count_date'   => ['nullable', 'date_format:Y-m-d'],
            'notes'        => ['nullable', 'string'],
        ]);

        $session = $this->startAction->execute(
            warehouseId: $data['warehouse_id'],
            locationId:  $data['location_id'] ?? null,
            countDate:   $data['count_date'] ?? null,
            notes:       $data['notes'] ?? null,
        );

        return response()->json($session, 201);
    }

    public function complete(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'counts'          => ['required', 'array'],
            'counts.*.entry_id'          => ['required', 'string'],
            'counts.*.counted_quantity'  => ['required', 'numeric', 'min:0'],
            'auto_adjust'     => ['nullable', 'boolean'],
        ]);

        $session = $this->completeAction->execute(
            sessionId:    $id,
            counts:       $data['counts'],
            autoAdjust:   (bool) ($data['auto_adjust'] ?? false),
        );

        return response()->json($session);
    }
}
