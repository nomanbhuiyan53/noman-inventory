<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Reporting;

use Illuminate\Database\Eloquent\Builder;
use Noman\Inventory\Application\DTOs\BatchExpiryResultDTO;
use Noman\Inventory\Application\DTOs\InventoryAgingResultDTO;
use Noman\Inventory\Application\DTOs\ReservationStatusResultDTO;
use Noman\Inventory\Application\DTOs\StockByLocationResultDTO;
use Noman\Inventory\Application\DTOs\StockCardResultDTO;
use Noman\Inventory\Application\DTOs\StockLedgerResultDTO;
use Noman\Inventory\Application\DTOs\StockOnHandResultDTO;
use Noman\Inventory\Application\DTOs\ValuationSummaryResultDTO;
use Noman\Inventory\Application\Queries\BatchExpiryQuery;
use Noman\Inventory\Application\Queries\InventoryAgingQuery;
use Noman\Inventory\Application\Queries\ReservationStatusQuery;
use Noman\Inventory\Application\Queries\StockByLocationQuery;
use Noman\Inventory\Application\Queries\StockCardQuery;
use Noman\Inventory\Application\Queries\StockLedgerQuery;
use Noman\Inventory\Application\Queries\StockOnHandQuery;
use Noman\Inventory\Application\Queries\ValuationSummaryQuery;
use Noman\Inventory\Contracts\InventoryReporterContract;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryBatchExpirySummary;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryInventoryAging;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryReservation;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockBalance;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockMovement;

/**
 * Eloquent-backed implementation of InventoryReporterContract.
 *
 * All methods read from the projection/read-model tables for fast reporting.
 * They return typed DTO arrays, never raw Eloquent models.
 */
final class EloquentInventoryReporter implements InventoryReporterContract
{
    public function getStockOnHand(StockOnHandQuery $query): array
    {
        return InventoryStockBalance::query()
            ->with(['item', 'warehouse', 'location'])
            ->forTenant($query->tenantId)
            ->when($query->itemId,      fn (Builder $q) => $q->where('item_id', $query->itemId))
            ->when($query->warehouseId, fn (Builder $q) => $q->where('warehouse_id', $query->warehouseId))
            ->when($query->locationId,  fn (Builder $q) => $q->where('location_id', $query->locationId))
            ->when(! $query->includeZeroBalance, fn (Builder $q) => $q->where('quantity_on_hand', '>', 0))
            ->when(! $query->includeNegativeBalance, fn (Builder $q) => $q->where('quantity_on_hand', '>=', 0))
            ->get()
            ->map(fn ($balance) => new StockOnHandResultDTO(
                itemId:              $balance->item_id,
                itemCode:            $balance->item?->code ?? '',
                itemName:            $balance->item?->name ?? '',
                quantityOnHand:      $balance->quantity_on_hand,
                quantityReserved:    $balance->quantity_reserved,
                quantityAvailable:   $balance->quantity_available,
                unitCode:            $balance->item?->unit?->code ?? '',
                warehouseId:         $balance->warehouse_id,
                warehouseName:       $balance->warehouse?->name,
                locationId:          $balance->location_id,
                locationCode:        $balance->location?->code,
                tenantId:            $balance->tenant_id,
            ))
            ->all();
    }

    public function getStockByLocation(StockByLocationQuery $query): array
    {
        return InventoryStockBalance::query()
            ->with(['item', 'warehouse', 'location'])
            ->forTenant($query->tenantId)
            ->when($query->itemId,      fn (Builder $q) => $q->where('item_id', $query->itemId))
            ->when($query->warehouseId, fn (Builder $q) => $q->where('warehouse_id', $query->warehouseId))
            ->where('quantity_on_hand', '>', 0)
            ->get()
            ->map(fn ($balance) => new StockByLocationResultDTO(
                itemId:           $balance->item_id,
                itemCode:         $balance->item?->code ?? '',
                warehouseId:      $balance->warehouse_id,
                warehouseName:    $balance->warehouse?->name ?? '',
                locationId:       $balance->location_id,
                locationCode:     $balance->location?->code,
                quantity:         $balance->quantity_on_hand,
                quantityReserved: $balance->quantity_reserved,
                tenantId:         $balance->tenant_id,
            ))
            ->all();
    }

    public function getStockLedger(StockLedgerQuery $query): array
    {
        return InventoryStockMovement::query()
            ->with(['item', 'warehouse', 'location', 'batch'])
            ->forTenant($query->tenantId)
            ->when($query->itemId,      fn (Builder $q) => $q->where('item_id', $query->itemId))
            ->when($query->warehouseId, fn (Builder $q) => $q->where('warehouse_id', $query->warehouseId))
            ->when($query->locationId,  fn (Builder $q) => $q->where('location_id', $query->locationId))
            ->when($query->batchId,     fn (Builder $q) => $q->where('batch_id', $query->batchId))
            ->when(! empty($query->movementTypes), fn (Builder $q) => $q->whereIn(
                'movement_type',
                array_map(fn ($t) => $t->value, $query->movementTypes)
            ))
            ->inDateRange($query->dateFrom, $query->dateTo)
            ->orderBy('posted_at')
            ->orderBy('id')
            ->forPage($query->page, $query->perPage)
            ->get()
            ->map(fn ($movement) => new StockLedgerResultDTO(
                movementId:      $movement->id,
                itemId:          $movement->item_id,
                itemCode:        $movement->item?->code ?? '',
                movementType:    $movement->movement_type->value,
                quantity:        $movement->quantity,
                runningBalance:  0.0, // computed separately via stock card
                documentNumber:  $movement->reference_document_number ?? '',
                postedAt:        $movement->posted_at?->toDateTimeString() ?? '',
                warehouseId:     $movement->warehouse_id,
                locationId:      $movement->location_id,
                batchCode:       $movement->batch?->batch_code,
                tenantId:        $movement->tenant_id,
            ))
            ->all();
    }

    public function getStockCard(StockCardQuery $query): array
    {
        $movements = InventoryStockMovement::query()
            ->forTenant($query->tenantId)
            ->forItem($query->itemId)
            ->when($query->warehouseId, fn (Builder $q) => $q->where('warehouse_id', $query->warehouseId))
            ->when($query->locationId,  fn (Builder $q) => $q->where('location_id', $query->locationId))
            ->inDateRange($query->dateFrom, $query->dateTo)
            ->with(['batch'])
            ->orderBy('posted_at')
            ->orderBy('id')
            ->get();

        $runningBalance = 0.0;
        $results        = [];

        foreach ($movements as $movement) {
            $runningBalance += $movement->quantity;
            $isIn = $movement->quantity >= 0;

            $results[] = new StockCardResultDTO(
                movementId:       $movement->id,
                movementType:     $movement->movement_type->value,
                movementTypeLabel:$movement->movement_type->label(),
                inQuantity:       $isIn ? $movement->quantity : 0,
                outQuantity:      $isIn ? 0 : abs($movement->quantity),
                balance:          $runningBalance,
                documentNumber:   $movement->reference_document_number ?? '',
                date:             $movement->posted_at?->toDateString() ?? '',
                batchCode:        $movement->batch?->batch_code,
            );
        }

        return $results;
    }

    public function getBatchExpiry(BatchExpiryQuery $query): array
    {
        return InventoryBatchExpirySummary::query()
            ->with(['item', 'batch', 'warehouse'])
            ->forTenant($query->tenantId ?? null)
            ->when($query->itemId,      fn (Builder $q) => $q->where('item_id', $query->itemId))
            ->when($query->warehouseId, fn (Builder $q) => $q->where('warehouse_id', $query->warehouseId))
            ->when(! $query->includeExpired, fn (Builder $q) => $q->where('is_expired', false))
            ->when($query->expiringWithinDays !== null, fn (Builder $q) => $q->where(
                'days_until_expiry', '<=', $query->expiringWithinDays
            ))
            ->where('quantity_on_hand', '>', 0)
            ->orderBy('expiry_date')
            ->get()
            ->map(fn ($summary) => new BatchExpiryResultDTO(
                batchId:         $summary->batch_id,
                batchCode:       $summary->batch?->batch_code ?? '',
                itemId:          $summary->item_id,
                itemCode:        $summary->item?->code ?? '',
                itemName:        $summary->item?->name ?? '',
                expiryDate:      $summary->expiry_date?->toDateString() ?? '',
                daysUntilExpiry: $summary->days_until_expiry,
                quantityOnHand:  $summary->quantity_on_hand,
                isExpired:       $summary->is_expired,
                warehouseId:     $summary->warehouse_id,
                tenantId:        $summary->tenant_id,
            ))
            ->all();
    }

    public function getInventoryAging(InventoryAgingQuery $query): array
    {
        return InventoryInventoryAging::query()
            ->with(['item', 'warehouse'])
            ->when($query->tenantId,    fn (Builder $q) => $q->where('tenant_id', $query->tenantId))
            ->when($query->itemId,      fn (Builder $q) => $q->where('item_id', $query->itemId))
            ->when($query->warehouseId, fn (Builder $q) => $q->where('warehouse_id', $query->warehouseId))
            ->get()
            ->map(fn ($aging) => new InventoryAgingResultDTO(
                itemId:          $aging->item_id,
                itemCode:        $aging->item?->code ?? '',
                itemName:        $aging->item?->name ?? '',
                qty0To30Days:    $aging->qty_0_to_30_days,
                qty31To60Days:   $aging->qty_31_to_60_days,
                qty61To90Days:   $aging->qty_61_to_90_days,
                qtyOver90Days:   $aging->qty_over_90_days,
                totalQuantity:   $aging->total_quantity,
                warehouseId:     $aging->warehouse_id,
                tenantId:        $aging->tenant_id,
            ))
            ->all();
    }

    public function getValuationSummary(ValuationSummaryQuery $query): array
    {
        return InventoryStockBalance::query()
            ->with(['item'])
            ->forTenant($query->tenantId)
            ->when($query->itemId,      fn (Builder $q) => $q->where('item_id', $query->itemId))
            ->when($query->warehouseId, fn (Builder $q) => $q->where('warehouse_id', $query->warehouseId))
            ->where('quantity_on_hand', '>', 0)
            ->get()
            ->map(fn ($balance) => new ValuationSummaryResultDTO(
                itemId:          $balance->item_id,
                itemCode:        $balance->item?->code ?? '',
                itemName:        $balance->item?->name ?? '',
                quantityOnHand:  $balance->quantity_on_hand,
                averageUnitCost: $balance->avg_cost ?? 0.0,
                totalValue:      $balance->total_value ?? 0.0,
                currency:        $balance->currency,
                valuationMethod: config('inventory.valuation_method', 'weighted_average'),
                warehouseId:     $balance->warehouse_id,
                tenantId:        $balance->tenant_id,
            ))
            ->all();
    }

    public function getReservationStatus(ReservationStatusQuery $query): array
    {
        return InventoryReservation::query()
            ->with(['item'])
            ->forTenant($query->tenantId)
            ->when($query->itemId,       fn (Builder $q) => $q->where('item_id', $query->itemId))
            ->when($query->referenceType, fn (Builder $q) => $q->where('reference_type', $query->referenceType))
            ->when($query->referenceId,  fn (Builder $q) => $q->where('reference_id', $query->referenceId))
            ->when($query->activeOnly,   fn (Builder $q) => $q->active())
            ->get()
            ->map(fn ($reservation) => new ReservationStatusResultDTO(
                reservationId:   $reservation->id,
                itemId:          $reservation->item_id,
                itemCode:        $reservation->item?->code ?? '',
                reservedQuantity:$reservation->quantity,
                status:          $reservation->status,
                referenceType:   $reservation->reference_type,
                referenceId:     $reservation->reference_id,
                expiresAt:       $reservation->expires_at?->toDateTimeString(),
                createdAt:       $reservation->created_at->toDateTimeString(),
                warehouseId:     $reservation->warehouse_id,
                tenantId:        $reservation->tenant_id,
            ))
            ->all();
    }
}
