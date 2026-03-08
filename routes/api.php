<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Noman\Inventory\Http\Controllers\ItemController;
use Noman\Inventory\Http\Controllers\WarehouseController;
use Noman\Inventory\Http\Controllers\LocationController;
use Noman\Inventory\Http\Controllers\StockReceiveController;
use Noman\Inventory\Http\Controllers\StockIssueController;
use Noman\Inventory\Http\Controllers\StockTransferController;
use Noman\Inventory\Http\Controllers\StockAdjustmentController;
use Noman\Inventory\Http\Controllers\ReservationController;
use Noman\Inventory\Http\Controllers\StockDocumentController;
use Noman\Inventory\Http\Controllers\StockCountController;
use Noman\Inventory\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Inventory API Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with the value of config('inventory.route_prefix')
| (default: 'inventory') and protected by the middleware configured in
| config('inventory.api_middleware').
|
| Set config('inventory.routes_enabled') to false to disable these routes
| and register your own.
|
*/

if (! config('inventory.routes_enabled', true)) {
    return;
}

$prefix     = config('inventory.route_prefix', 'inventory');
$middleware = config('inventory.api_middleware', ['api']);

Route::prefix($prefix)
    ->middleware($middleware)
    ->name('inventory.')
    ->group(function () {

        // -----------------------------------------------------------------------
        // Catalog — Items
        // -----------------------------------------------------------------------
        Route::apiResource('items', ItemController::class);

        // -----------------------------------------------------------------------
        // Locations — Warehouses & Locations
        // -----------------------------------------------------------------------
        Route::apiResource('warehouses', WarehouseController::class);
        Route::apiResource('warehouses.locations', LocationController::class)
            ->shallow();

        // -----------------------------------------------------------------------
        // Stock Operations
        // -----------------------------------------------------------------------
        Route::post('stock/receive',              [StockReceiveController::class,   'store'])->name('stock.receive');
        Route::post('stock/issue',                [StockIssueController::class,     'store'])->name('stock.issue');
        Route::post('stock/transfer',             [StockTransferController::class,  'store'])->name('stock.transfer');
        Route::post('stock/adjust',               [StockAdjustmentController::class,'store'])->name('stock.adjust');

        // -----------------------------------------------------------------------
        // Reservations
        // -----------------------------------------------------------------------
        Route::post('stock/reserve',              [ReservationController::class, 'store'])->name('stock.reserve');
        Route::delete('stock/reserve/{id}',       [ReservationController::class, 'destroy'])->name('stock.release');

        // -----------------------------------------------------------------------
        // Documents (list, show, post, reverse)
        // -----------------------------------------------------------------------
        Route::get('documents',                   [StockDocumentController::class, 'index'])->name('documents.index');
        Route::get('documents/{id}',              [StockDocumentController::class, 'show'])->name('documents.show');
        Route::post('documents/{id}/post',        [StockDocumentController::class, 'post'])->name('documents.post');
        Route::post('documents/{id}/reverse',     [StockDocumentController::class, 'reverse'])->name('documents.reverse');
        Route::delete('documents/{id}',           [StockDocumentController::class, 'cancel'])->name('documents.cancel');

        // -----------------------------------------------------------------------
        // Stock Counts
        // -----------------------------------------------------------------------
        Route::post('stock-counts/start',         [StockCountController::class, 'start'])->name('stock-counts.start');
        Route::post('stock-counts/{id}/complete', [StockCountController::class, 'complete'])->name('stock-counts.complete');
        Route::get('stock-counts',                [StockCountController::class, 'index'])->name('stock-counts.index');
        Route::get('stock-counts/{id}',           [StockCountController::class, 'show'])->name('stock-counts.show');

        // -----------------------------------------------------------------------
        // Reports
        // -----------------------------------------------------------------------
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('stock-on-hand',      [ReportController::class, 'stockOnHand'])->name('stock-on-hand');
            Route::get('stock-by-location',  [ReportController::class, 'stockByLocation'])->name('stock-by-location');
            Route::get('stock-ledger',       [ReportController::class, 'stockLedger'])->name('stock-ledger');
            Route::get('stock-card',         [ReportController::class, 'stockCard'])->name('stock-card');
            Route::get('batch-expiry',       [ReportController::class, 'batchExpiry'])->name('batch-expiry');
            Route::get('inventory-aging',    [ReportController::class, 'inventoryAging'])->name('inventory-aging');
            Route::get('valuation-summary',  [ReportController::class, 'valuationSummary'])->name('valuation-summary');
            Route::get('reservations',       [ReportController::class, 'reservationStatus'])->name('reservation-status');
        });
    });
