<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryWarehouse;

class WarehouseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId  = $request->get('tenant_id');
        $warehouses = InventoryWarehouse::query()
            ->forTenant($tenantId)
            ->when($request->boolean('active'), fn ($q) => $q->active())
            ->paginate($request->get('per_page', 20));

        return response()->json($warehouses);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'code'      => ['required', 'string', 'max:50'],
            'address'   => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'is_virtual'=> ['nullable', 'boolean'],
            'metadata'  => ['nullable', 'array'],
        ]);

        $warehouse = InventoryWarehouse::create(array_merge(
            $validated,
            ['id' => (string) Str::ulid()]
        ));

        return response()->json($warehouse, 201);
    }

    public function show(string $id): JsonResponse
    {
        return response()->json(InventoryWarehouse::with('locations')->findOrFail($id));
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $warehouse = InventoryWarehouse::findOrFail($id);
        $warehouse->update($request->validate([
            'name'      => ['sometimes', 'string', 'max:255'],
            'address'   => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'metadata'  => ['nullable', 'array'],
        ]));

        return response()->json($warehouse);
    }

    public function destroy(string $id): JsonResponse
    {
        InventoryWarehouse::findOrFail($id)->delete();

        return response()->json(['message' => 'Warehouse deleted.']);
    }
}
