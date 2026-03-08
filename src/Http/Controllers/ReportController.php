<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Noman\Inventory\Application\Queries\BatchExpiryQuery;
use Noman\Inventory\Application\Queries\InventoryAgingQuery;
use Noman\Inventory\Application\Queries\ReservationStatusQuery;
use Noman\Inventory\Application\Queries\StockByLocationQuery;
use Noman\Inventory\Application\Queries\StockCardQuery;
use Noman\Inventory\Application\Queries\StockLedgerQuery;
use Noman\Inventory\Application\Queries\StockOnHandQuery;
use Noman\Inventory\Application\Queries\ValuationSummaryQuery;
use Noman\Inventory\Contracts\InventoryReporterContract;

class ReportController extends Controller
{
    public function __construct(
        private readonly InventoryReporterContract $reporter,
    ) {}

    public function stockOnHand(Request $request): JsonResponse
    {
        $results = $this->reporter->getStockOnHand(new StockOnHandQuery(
            itemId:               $request->get('item_id'),
            itemTypeId:           $request->get('item_type_id'),
            categoryId:           $request->get('category_id'),
            warehouseId:          $request->get('warehouse_id'),
            locationId:           $request->get('location_id'),
            tenantId:             $request->get('tenant_id'),
            includeZeroBalance:   $request->boolean('include_zero'),
            includeNegativeBalance:$request->boolean('include_negative'),
        ));

        return response()->json(['data' => $results]);
    }

    public function stockByLocation(Request $request): JsonResponse
    {
        $results = $this->reporter->getStockByLocation(new StockByLocationQuery(
            itemId:      $request->get('item_id'),
            warehouseId: $request->get('warehouse_id'),
            tenantId:    $request->get('tenant_id'),
        ));

        return response()->json(['data' => $results]);
    }

    public function stockLedger(Request $request): JsonResponse
    {
        $results = $this->reporter->getStockLedger(new StockLedgerQuery(
            itemId:      $request->get('item_id'),
            warehouseId: $request->get('warehouse_id'),
            locationId:  $request->get('location_id'),
            batchId:     $request->get('batch_id'),
            dateFrom:    $request->get('date_from'),
            dateTo:      $request->get('date_to'),
            tenantId:    $request->get('tenant_id'),
            perPage:     (int) $request->get('per_page', 50),
            page:        (int) $request->get('page', 1),
        ));

        return response()->json(['data' => $results]);
    }

    public function stockCard(Request $request): JsonResponse
    {
        $results = $this->reporter->getStockCard(new StockCardQuery(
            itemId:      $request->input('item_id', ''),
            warehouseId: $request->get('warehouse_id'),
            locationId:  $request->get('location_id'),
            dateFrom:    $request->get('date_from'),
            dateTo:      $request->get('date_to'),
            tenantId:    $request->get('tenant_id'),
        ));

        return response()->json(['data' => $results]);
    }

    public function batchExpiry(Request $request): JsonResponse
    {
        $results = $this->reporter->getBatchExpiry(new BatchExpiryQuery(
            itemId:             $request->get('item_id'),
            warehouseId:        $request->get('warehouse_id'),
            expiringWithinDays: $request->integer('expiring_within_days', 30),
            includeExpired:     $request->boolean('include_expired'),
            tenantId:           $request->get('tenant_id'),
        ));

        return response()->json(['data' => $results]);
    }

    public function inventoryAging(Request $request): JsonResponse
    {
        $results = $this->reporter->getInventoryAging(new InventoryAgingQuery(
            itemId:      $request->get('item_id'),
            warehouseId: $request->get('warehouse_id'),
            tenantId:    $request->get('tenant_id'),
        ));

        return response()->json(['data' => $results]);
    }

    public function valuationSummary(Request $request): JsonResponse
    {
        $results = $this->reporter->getValuationSummary(new ValuationSummaryQuery(
            itemId:      $request->get('item_id'),
            warehouseId: $request->get('warehouse_id'),
            dateAsOf:    $request->get('date_as_of'),
            tenantId:    $request->get('tenant_id'),
        ));

        return response()->json(['data' => $results]);
    }

    public function reservationStatus(Request $request): JsonResponse
    {
        $results = $this->reporter->getReservationStatus(new ReservationStatusQuery(
            itemId:        $request->get('item_id'),
            referenceType: $request->get('reference_type'),
            referenceId:   $request->get('reference_id'),
            activeOnly:    $request->boolean('active_only', true),
            tenantId:      $request->get('tenant_id'),
        ));

        return response()->json(['data' => $results]);
    }
}
