<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Controllers\Web;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Noman\Inventory\Http\Requests\StoreItemRequest;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryItem;

class ItemWebController extends Controller
{
    public function index(Request $request): View
    {
        $items = InventoryItem::query()
            ->forTenant($request->get('tenant_id'))
            ->when($request->boolean('active'), fn ($q) => $q->active())
            ->when($request->get('search'), fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('name', 'like', '%' . $request->get('search') . '%')
                          ->orWhere('code', 'like', '%' . $request->get('search') . '%')
                          ->orWhere('sku', 'like', '%' . $request->get('search') . '%')
                )
            )
            ->orderBy('code')
            ->paginate($request->get('per_page', 20));

        return view('noman-inventory::items.index', compact('items'));
    }

    public function create(): View
    {
        return view('noman-inventory::items.create');
    }

    public function store(StoreItemRequest $request): RedirectResponse
    {
        InventoryItem::create(array_merge(
            $request->validated(),
            ['id' => (string) \Illuminate\Support\Str::ulid()]
        ));

        return redirect()->route('inventory.items.index')->with('success', 'Item created.');
    }

    public function show(string $item): View
    {
        $item = InventoryItem::with(['itemType', 'category', 'unit'])->findOrFail($item);

        return view('noman-inventory::items.show', compact('item'));
    }

    public function edit(string $item): View
    {
        $item = InventoryItem::findOrFail($item);

        return view('noman-inventory::items.edit', compact('item'));
    }

    public function update(StoreItemRequest $request, string $item): RedirectResponse
    {
        $item = InventoryItem::findOrFail($item);
        $item->update($request->validated());

        return redirect()->route('inventory.items.show', $item)->with('success', 'Item updated.');
    }

    public function destroy(string $item): RedirectResponse
    {
        InventoryItem::findOrFail($item)->delete();

        return redirect()->route('inventory.items.index')->with('success', 'Item deleted.');
    }
}
