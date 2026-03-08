<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Noman\Inventory\Http\Controllers\Web\DashboardController;
use Noman\Inventory\Http\Controllers\Web\DocumentWebController;
use Noman\Inventory\Http\Controllers\Web\ItemWebController;
use Noman\Inventory\Http\Controllers\Web\ReportWebController;
use Noman\Inventory\Http\Controllers\Web\StockCountWebController;
use Noman\Inventory\Http\Controllers\Web\StockWebController;
use Noman\Inventory\Http\Controllers\Web\WarehouseWebController;

/*
|--------------------------------------------------------------------------
| Inventory Web (Blade) Routes
|--------------------------------------------------------------------------
|
| Set config('inventory.routes_enabled') to false to disable.
| Prefix and middleware from config('inventory.route_prefix') and
| config('inventory.web_middleware') if set, otherwise 'web'.
|
*/

if (! config('inventory.routes_enabled', true)) {
    return;
}

$prefix   = config('inventory.route_prefix', 'inventory');
$middleware = config('inventory.web_middleware', ['web']);

Route::prefix($prefix)
    ->middleware($middleware)
    ->name('inventory.')
    ->group(function () {

        Route::get('/', DashboardController::class)->name('dashboard');

        // Items
        Route::resource('items', ItemWebController::class)->names('items');

        // Warehouses
        Route::resource('warehouses', WarehouseWebController::class)->names('warehouses');

        // Stock operations (form + submit)
        Route::get('stock/receive', [StockWebController::class, 'receive'])->name('stock.receive');
        Route::post('stock/receive', [StockWebController::class, 'receiveSubmit'])->name('stock.receive.submit');
        Route::get('stock/issue', [StockWebController::class, 'issue'])->name('stock.issue');
        Route::post('stock/issue', [StockWebController::class, 'issueSubmit'])->name('stock.issue.submit');
        Route::get('stock/transfer', [StockWebController::class, 'transfer'])->name('stock.transfer');
        Route::post('stock/transfer', [StockWebController::class, 'transferSubmit'])->name('stock.transfer.submit');
        Route::get('stock/adjust', [StockWebController::class, 'adjust'])->name('stock.adjust');
        Route::post('stock/adjust', [StockWebController::class, 'adjustSubmit'])->name('stock.adjust.submit');

        // Documents
        Route::get('documents', [DocumentWebController::class, 'index'])->name('documents.index');
        Route::get('documents/{id}', [DocumentWebController::class, 'show'])->name('documents.show');

        // Stock counts
        Route::get('stock-counts', [StockCountWebController::class, 'index'])->name('stock-counts.index');
        Route::get('stock-counts/start', [StockCountWebController::class, 'start'])->name('stock-counts.start');
        Route::post('stock-counts/start', [StockCountWebController::class, 'startSubmit'])->name('stock-counts.start.submit');
        Route::get('stock-counts/{id}', [StockCountWebController::class, 'show'])->name('stock-counts.show');

        // Reports
        Route::get('reports', [ReportWebController::class, 'index'])->name('reports.index');
        Route::get('reports/stock-on-hand', [ReportWebController::class, 'stockOnHand'])->name('reports.stock-on-hand');
        Route::get('reports/stock-by-location', [ReportWebController::class, 'stockByLocation'])->name('reports.stock-by-location');
        Route::get('reports/stock-ledger', [ReportWebController::class, 'stockLedger'])->name('reports.stock-ledger');
        Route::get('reports/batch-expiry', [ReportWebController::class, 'batchExpiry'])->name('reports.batch-expiry');
        Route::get('reports/reservations', [ReportWebController::class, 'reservations'])->name('reports.reservations');
    });
