<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Noman\Inventory\Http\Requests\StoreItemRequest;
use Noman\Inventory\Http\Resources\ItemResource;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryItem;

class ItemController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $tenantId = $request->get('tenant_id');

        $items = InventoryItem::query()
            ->forTenant($tenantId)
            ->when($request->get('active'), fn ($q) => $q->active())
            ->when($request->get('search'), fn ($q, $search) =>
                $q->where(fn ($inner) =>
                    $inner->where('name', 'like', "%{$search}%")
                          ->orWhere('code', 'like', "%{$search}%")
                          ->orWhere('sku', 'like', "%{$search}%")
                )
            )
            ->with(['itemType', 'category', 'unit'])
            ->paginate($request->get('per_page', 20));

        return ItemResource::collection($items);
    }

    public function store(StoreItemRequest $request): ItemResource
    {
        $item = InventoryItem::create(array_merge(
            $request->validated(),
            ['id' => (string) Str::ulid()]
        ));

        return new ItemResource($item->load(['itemType', 'category', 'unit']));
    }

    public function show(string $id): ItemResource
    {
        $item = InventoryItem::with(['itemType', 'category', 'unit', 'variants'])->findOrFail($id);

        return new ItemResource($item);
    }

    public function update(StoreItemRequest $request, string $id): ItemResource
    {
        $item = InventoryItem::findOrFail($id);
        $item->update($request->validated());

        return new ItemResource($item->load(['itemType', 'category', 'unit']));
    }

    public function destroy(string $id): JsonResponse
    {
        InventoryItem::findOrFail($id)->delete();

        return response()->json(['message' => 'Item deleted.']);
    }
}
