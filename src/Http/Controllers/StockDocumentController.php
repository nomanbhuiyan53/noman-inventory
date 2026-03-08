<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Noman\Inventory\Application\DTOs\ReverseDocumentDTO;
use Noman\Inventory\Contracts\InventoryManagerContract;
use Noman\Inventory\Domain\Shared\Enums\DocumentStatus;
use Noman\Inventory\Http\Requests\ReverseDocumentRequest;
use Noman\Inventory\Http\Resources\StockDocumentResource;
use Noman\Inventory\Http\Resources\StockDocumentResultResource;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockDocument;

class StockDocumentController extends Controller
{
    public function __construct(
        private readonly InventoryManagerContract $inventory,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $documents = InventoryStockDocument::query()
            ->forTenant($request->get('tenant_id'))
            ->when($request->get('type'),   fn ($q) => $q->ofType($request->get('type')))
            ->when($request->get('status'), fn ($q) => $q->where('status', $request->get('status')))
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 20));

        return response()->json(StockDocumentResource::collection($documents)->response()->getData(true));
    }

    public function show(string $id): StockDocumentResource
    {
        return new StockDocumentResource(
            InventoryStockDocument::with(['lines', 'lines.item', 'lines.batch'])->findOrFail($id)
        );
    }

    public function post(string $id): JsonResponse
    {
        // TODO: Implement full PostStockDocumentAction that handles draft/approved → posted transition.
        // For now, use this simplified handler.
        $document = InventoryStockDocument::findOrFail($id);

        if (! $document->canBePosted()) {
            return response()->json([
                'message' => "Document cannot be posted in status '{$document->status->value}'.",
            ], 422);
        }

        $document->status    = DocumentStatus::Posted->value;
        $document->posted_at = now();
        $document->save();

        return response()->json(['message' => 'Document posted.', 'document_id' => $id]);
    }

    public function reverse(ReverseDocumentRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();

        $result = $this->inventory->reverseDocument(new ReverseDocumentDTO(
            documentId: $id,
            reason:     $data['reason'],
            notes:      $data['notes'] ?? null,
        ));

        return response()->json(
            (new StockDocumentResultResource($result))->toArray($request)
        );
    }

    public function cancel(string $id): JsonResponse
    {
        $document = InventoryStockDocument::findOrFail($id);

        if ($document->status->hasLedgerEntries()) {
            return response()->json(['message' => 'Posted documents cannot be cancelled. Use reverse.'], 422);
        }

        $document->status       = DocumentStatus::Cancelled->value;
        $document->cancelled_at = now();
        $document->save();

        return response()->json(['message' => 'Document cancelled.']);
    }
}
