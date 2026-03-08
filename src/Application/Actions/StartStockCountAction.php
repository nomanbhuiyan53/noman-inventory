<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\Actions;

use Illuminate\Support\Str;
use Noman\Inventory\Contracts\DocumentNumberGeneratorContract;
use Noman\Inventory\Contracts\TenantResolverContract;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockCountEntry;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockCountSession;
use Noman\Inventory\Infrastructure\Persistence\Repositories\StockBalanceRepository;

/**
 * Starts a new stock count session for a warehouse.
 *
 * On start, expected quantities are captured from the current stock_balances projection
 * for all items in the warehouse (or filtered by location). These become the baseline
 * for variance calculation when the count is completed.
 *
 * @see CompleteStockCountAction
 */
final class StartStockCountAction
{
    public function __construct(
        private readonly TenantResolverContract $tenantResolver,
        private readonly DocumentNumberGeneratorContract $docNumberGenerator,
        private readonly StockBalanceRepository $balanceRepository,
    ) {}

    /**
     * @param  string       $warehouseId    Warehouse to count
     * @param  string|null  $locationId     Restrict to a specific location (optional)
     * @param  string|null  $countDate      Date of count in Y-m-d format (defaults to today)
     * @param  string|null  $notes          Notes
     * @param  string|null  $createdBy      User reference
     * @param  string|null  $tenantId       Tenant scope
     * @return InventoryStockCountSession
     */
    public function execute(
        string $warehouseId,
        ?string $locationId = null,
        ?string $countDate = null,
        ?string $notes = null,
        ?string $createdBy = null,
        ?string $tenantId = null,
    ): InventoryStockCountSession {
        $tenantId  = $tenantId ?? $this->tenantResolver->getCurrentTenantId()?->getValue();
        $docNumber = $this->docNumberGenerator->generate('stock_count', $tenantId);

        $session = InventoryStockCountSession::create([
            'id'            => (string) Str::ulid(),
            'tenant_id'     => $tenantId,
            'session_number'=> $docNumber->getValue(),
            'warehouse_id'  => $warehouseId,
            'location_id'   => $locationId,
            'status'        => 'in_progress',
            'count_date'    => $countDate ?? now()->toDateString(),
            'notes'         => $notes,
            'created_by'    => $createdBy,
        ]);

        // Capture expected quantities from the balance projection for all items
        // in this warehouse (or location).
        $balances = \Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockBalance::query()
            ->forTenant($tenantId)
            ->forWarehouse($warehouseId)
            ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
            ->withPositiveStock()
            ->get();

        foreach ($balances as $balance) {
            InventoryStockCountEntry::create([
                'id'                => (string) Str::ulid(),
                'tenant_id'         => $tenantId,
                'session_id'        => $session->id,
                'item_id'           => $balance->item_id,
                'batch_id'          => $balance->batch_id,
                'location_id'       => $balance->location_id,
                'expected_quantity' => $balance->quantity_on_hand,
                'counted_quantity'  => null,
            ]);
        }

        event(new \Noman\Inventory\Domain\Inventory\Events\StockCountStarted(
            sessionId:   $session->id,
            warehouseId: $warehouseId,
            tenantId:    $tenantId,
        ));

        return $session;
    }
}
