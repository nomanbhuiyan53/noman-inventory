<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockDocument;

class DocumentWebController extends Controller
{
    public function index(Request $request): View
    {
        $documents = InventoryStockDocument::query()
            ->forTenant($request->get('tenant_id'))
            ->with(['lines.item', 'lines.warehouse', 'lines.batch'])
            ->orderByDesc('document_date')
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 20));

        return view('noman-inventory::documents.index', compact('documents'));
    }

    public function show(string $id): View
    {
        $document = InventoryStockDocument::with(['lines.item', 'lines.warehouse', 'lines.batch'])->findOrFail($id);

        return view('noman-inventory::documents.show', compact('document'));
    }
}
