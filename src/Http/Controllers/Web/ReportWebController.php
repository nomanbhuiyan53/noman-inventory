<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Noman\Inventory\Application\Queries\BatchExpiryQuery;
use Noman\Inventory\Application\Queries\ReservationStatusQuery;
use Noman\Inventory\Application\Queries\StockByLocationQuery;
use Noman\Inventory\Application\Queries\StockLedgerQuery;
use Noman\Inventory\Application\Queries\StockOnHandQuery;
use Noman\Inventory\Contracts\InventoryReporterContract;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryWarehouse;

class ReportWebController extends Controller
{
    public function __construct(
        private readonly InventoryReporterContract $reporter,
    ) {}

    public function index(): View
    {
        return view('noman-inventory::reports.index');
    }

    public function stockOnHand(Request $request): View
    {
        $warehouses = InventoryWarehouse::query()->active()->orderBy('code')->get();
        $query     = new StockOnHandQuery(
            warehouseId: $request->get('warehouse_id'),
            tenantId:   $request->get('tenant_id'),
            includeZeroBalance: $request->boolean('include_zero'),
        );
        $results = $this->reporter->getStockOnHand($query);

        return view('noman-inventory::reports.stock-on-hand', compact('results', 'warehouses'));
    }

    public function stockByLocation(Request $request): View
    {
        $query   = new StockByLocationQuery(
            warehouseId: $request->get('warehouse_id'),
            tenantId:   $request->get('tenant_id'),
        );
        $results = $this->reporter->getStockByLocation($query);

        return view('noman-inventory::reports.stock-by-location', compact('results'));
    }

    public function stockLedger(Request $request): View
    {
        $query   = new StockLedgerQuery(
            itemId:     $request->get('item_id'),
            warehouseId: $request->get('warehouse_id'),
            dateFrom:   $request->get('date_from'),
            dateTo:     $request->get('date_to'),
            tenantId:   $request->get('tenant_id'),
            perPage:    (int) $request->get('per_page', 50),
            page:       (int) $request->get('page', 1),
        );
        $results = $this->reporter->getStockLedger($query);

        return view('noman-inventory::reports.stock-ledger', compact('results'));
    }

    public function batchExpiry(Request $request): View
    {
        $query   = new BatchExpiryQuery(
            warehouseId: $request->get('warehouse_id'),
            tenantId:   $request->get('tenant_id'),
            includeExpired: $request->boolean('include_expired', true),
        );
        $results = $this->reporter->getBatchExpiry($query);

        return view('noman-inventory::reports.batch-expiry', compact('results'));
    }

    public function reservations(Request $request): View
    {
        $query   = new ReservationStatusQuery(
            itemId:       $request->get('item_id'),
            referenceType: $request->get('reference_type'),
            referenceId:  $request->get('reference_id'),
            activeOnly:   $request->boolean('active_only', true),
            tenantId:    $request->get('tenant_id'),
        );
        $results = $this->reporter->getReservationStatus($query);

        return view('noman-inventory::reports.reservations', compact('results'));
    }
}
