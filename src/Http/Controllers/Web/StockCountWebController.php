<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Controllers\Web;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Noman\Inventory\Application\Actions\StartStockCountAction;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockCountSession;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryWarehouse;

class StockCountWebController extends Controller
{
    public function __construct(
        private readonly StartStockCountAction $startStockCountAction,
    ) {}

    public function index(Request $request): View
    {
        $sessions = InventoryStockCountSession::query()
            ->forTenant($request->get('tenant_id'))
            ->with('warehouse')
            ->orderByDesc('count_date')
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 20));

        return view('noman-inventory::stock-counts.index', compact('sessions'));
    }

    public function show(string $id): View
    {
        $session = InventoryStockCountSession::with(['warehouse', 'entries.item'])->findOrFail($id);

        return view('noman-inventory::stock-counts.show', compact('session'));
    }

    public function start(Request $request): View
    {
        $warehouses = InventoryWarehouse::query()->active()->orderBy('code')->get();

        return view('noman-inventory::stock-counts.start', compact('warehouses'));
    }

    public function startSubmit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'warehouse_id' => ['required', 'string'],
            'count_date'   => ['required', 'date'],
        ]);

        $session = $this->startStockCountAction->execute(
            warehouseId: $data['warehouse_id'],
            countDate:   $data['count_date'],
        );

        return redirect()
            ->route('inventory.stock-counts.show', $session->id)
            ->with('success', 'Stock count started: ' . $session->session_number);
    }
}
