<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Controllers\Web;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryWarehouse;

class WarehouseWebController extends Controller
{
    public function index(Request $request): View
    {
        $warehouses = InventoryWarehouse::query()
            ->forTenant($request->get('tenant_id'))
            ->when($request->boolean('active'), fn ($q) => $q->active())
            ->orderBy('code')
            ->paginate($request->get('per_page', 20));

        return view('noman-inventory::warehouses.index', compact('warehouses'));
    }

    public function create(): View
    {
        return view('noman-inventory::warehouses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'code'      => ['required', 'string', 'max:50'],
            'address'   => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        InventoryWarehouse::create(array_merge(
            $validated,
            ['id' => (string) \Illuminate\Support\Str::ulid(), 'is_active' => $request->boolean('is_active', true)]
        ));

        return redirect()->route('inventory.warehouses.index')->with('success', 'Warehouse created.');
    }

    public function show(string $warehouse): View
    {
        $warehouse = InventoryWarehouse::with('locations')->findOrFail($warehouse);

        return view('noman-inventory::warehouses.show', compact('warehouse'));
    }

    public function edit(string $warehouse): View
    {
        $warehouse = InventoryWarehouse::findOrFail($warehouse);

        return view('noman-inventory::warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, string $warehouse): RedirectResponse
    {
        $warehouse = InventoryWarehouse::findOrFail($warehouse);
        $warehouse->update($request->validate([
            'name'      => ['sometimes', 'string', 'max:255'],
            'code'      => ['sometimes', 'string', 'max:50'],
            'address'   => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]));

        return redirect()->route('inventory.warehouses.show', $warehouse)->with('success', 'Warehouse updated.');
    }

    public function destroy(string $warehouse): RedirectResponse
    {
        InventoryWarehouse::findOrFail($warehouse)->delete();

        return redirect()->route('inventory.warehouses.index')->with('success', 'Warehouse deleted.');
    }
}
