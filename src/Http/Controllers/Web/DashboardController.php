<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Controllers\Web;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryItem;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockDocument;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryWarehouse;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $itemsCount = InventoryItem::count();
        $warehousesCount = InventoryWarehouse::count();
        $documentsCount = InventoryStockDocument::query()->posted()->count();
        $recentDocuments = InventoryStockDocument::query()
            ->posted()
            ->orderByDesc('posted_at')
            ->limit(10)
            ->get(['id', 'document_number', 'document_type', 'posted_at']);

        return view('noman-inventory::dashboard', compact(
            'itemsCount',
            'warehousesCount',
            'documentsCount',
            'recentDocuments'
        ));
    }
}
