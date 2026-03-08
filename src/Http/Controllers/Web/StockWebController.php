<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Controllers\Web;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Noman\Inventory\Application\DTOs\AdjustStockDTO;
use Noman\Inventory\Application\DTOs\IssueStockDTO;
use Noman\Inventory\Application\DTOs\ReceiveStockDTO;
use Noman\Inventory\Application\DTOs\TransferStockDTO;
use Noman\Inventory\Contracts\InventoryManagerContract;
use Noman\Inventory\Domain\Shared\Enums\MovementType;
use Noman\Inventory\Domain\Shared\ValueObjects\Money;
use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryItem;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryWarehouse;

class StockWebController extends Controller
{
    public function __construct(
        private readonly InventoryManagerContract $inventory,
    ) {}

    private function itemsAndWarehouses(): array
    {
        $items = InventoryItem::query()->active()->stockable()->orderBy('code')->get();
        $warehouses = InventoryWarehouse::query()->active()->orderBy('code')->get();

        return compact('items', 'warehouses');
    }

    public function receive(Request $request): View
    {
        return view('noman-inventory::stock.receive', $this->itemsAndWarehouses());
    }

    public function receiveSubmit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'item_id'              => ['required', 'string'],
            'warehouse_id'         => ['required', 'string'],
            'quantity'             => ['required', 'numeric', 'min:0.0001'],
            'unit_cost'            => ['nullable', 'numeric', 'min:0'],
            'batch_code'           => ['nullable', 'string', 'max:100'],
            'expiry_date'          => ['nullable', 'date'],
            'reference_doc_number' => ['nullable', 'string', 'max:64'],
        ]);

        $unitCost = isset($data['unit_cost']) && (float) $data['unit_cost'] >= 0
            ? Money::of((float) $data['unit_cost'], config('inventory.currency', 'USD'))
            : null;

        $dto = new ReceiveStockDTO(
            itemId:             $data['item_id'],
            quantity:           Quantity::of((float) $data['quantity']),
            warehouseId:        $data['warehouse_id'],
            unitCost:           $unitCost,
            batchCode:          $data['batch_code'] ?? null,
            expiryDate:         isset($data['expiry_date']) ? date('Y-m-d', strtotime($data['expiry_date'])) : null,
            referenceDocNumber: $data['reference_doc_number'] ?? null,
        );

        $result = $this->inventory->receive($dto);

        return redirect()
            ->route('inventory.stock.receive')
            ->with('success', 'Stock received. Document: ' . $result->documentNumber);
    }

    public function issue(Request $request): View
    {
        return view('noman-inventory::stock.issue', $this->itemsAndWarehouses());
    }

    public function issueSubmit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'item_id'              => ['required', 'string'],
            'warehouse_id'         => ['required', 'string'],
            'quantity'             => ['required', 'numeric', 'min:0.0001'],
            'movement_type'        => ['required', 'string', 'in:sale_out,consumption_out,wastage_out,transfer_out,return_out,expired_out,dead_stock_out'],
            'reference_doc_number' => ['nullable', 'string', 'max:64'],
        ]);

        $movementType = MovementType::from($data['movement_type']);

        $dto = new IssueStockDTO(
            itemId:             $data['item_id'],
            quantity:            Quantity::of((float) $data['quantity']),
            warehouseId:        $data['warehouse_id'],
            movementType:       $movementType,
            referenceDocNumber: $data['reference_doc_number'] ?? null,
        );

        $result = $this->inventory->issue($dto);

        return redirect()
            ->route('inventory.stock.issue')
            ->with('success', 'Stock issued. Document: ' . $result->documentNumber);
    }

    public function transfer(Request $request): View
    {
        return view('noman-inventory::stock.transfer', $this->itemsAndWarehouses());
    }

    public function transferSubmit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'item_id'              => ['required', 'string'],
            'quantity'             => ['required', 'numeric', 'min:0.0001'],
            'from_warehouse_id'    => ['required', 'string'],
            'to_warehouse_id'      => ['required', 'string', 'different:from_warehouse_id'],
            'reference_doc_number' => ['nullable', 'string', 'max:64'],
        ]);

        $dto = new TransferStockDTO(
            itemId:             $data['item_id'],
            quantity:           Quantity::of((float) $data['quantity']),
            fromWarehouseId:    $data['from_warehouse_id'],
            toWarehouseId:      $data['to_warehouse_id'],
            referenceDocNumber: $data['reference_doc_number'] ?? null,
        );

        $result = $this->inventory->transfer($dto);

        return redirect()
            ->route('inventory.stock.transfer')
            ->with('success', 'Stock transferred. Document: ' . $result->documentNumber);
    }

    public function adjust(Request $request): View
    {
        return view('noman-inventory::stock.adjust', $this->itemsAndWarehouses());
    }

    public function adjustSubmit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'item_id'      => ['required', 'string'],
            'warehouse_id' => ['required', 'string'],
            'quantity'     => ['required', 'numeric'],
            'reason'       => ['nullable', 'string', 'max:255'],
        ]);

        $dto = new AdjustStockDTO(
            itemId:      $data['item_id'],
            quantity:    Quantity::of((float) $data['quantity']),
            warehouseId: $data['warehouse_id'],
            reason:      $data['reason'] ?? null,
        );

        $result = $this->inventory->adjust($dto);

        return redirect()
            ->route('inventory.stock.adjust')
            ->with('success', 'Stock adjusted. Document: ' . $result->documentNumber);
    }
}
