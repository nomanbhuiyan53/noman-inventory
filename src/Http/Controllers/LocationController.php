<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryLocation;

class LocationController extends Controller
{
    public function index(Request $request, string $warehouseId): JsonResponse
    {
        $locations = InventoryLocation::query()
            ->where('warehouse_id', $warehouseId)
            ->paginate($request->get('per_page', 50));

        return response()->json($locations);
    }

    public function store(Request $request, string $warehouseId): JsonResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'code'      => ['required', 'string', 'max:50'],
            'type'      => ['nullable', 'string', 'in:general,zone,aisle,rack,shelf,bin'],
            'parent_id' => ['nullable', 'string'],
            'barcode'   => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'metadata'  => ['nullable', 'array'],
        ]);

        $location = InventoryLocation::create(array_merge(
            $validated,
            ['id' => (string) Str::ulid(), 'warehouse_id' => $warehouseId]
        ));

        return response()->json($location, 201);
    }

    public function show(string $id): JsonResponse
    {
        return response()->json(InventoryLocation::with(['warehouse', 'children'])->findOrFail($id));
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $location = InventoryLocation::findOrFail($id);
        $location->update($request->validate([
            'name'      => ['sometimes', 'string', 'max:255'],
            'code'      => ['sometimes', 'string', 'max:50'],
            'type'      => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]));

        return response()->json($location);
    }

    public function destroy(string $id): JsonResponse
    {
        InventoryLocation::findOrFail($id)->delete();

        return response()->json(['message' => 'Location deleted.']);
    }
}
