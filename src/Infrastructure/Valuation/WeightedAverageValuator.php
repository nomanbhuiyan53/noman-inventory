<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Valuation;

use Illuminate\Support\Str;
use Noman\Inventory\Application\DTOs\ValuationEntryDTO;
use Noman\Inventory\Contracts\StockValuatorContract;
use Noman\Inventory\Domain\Shared\Enums\MovementType;
use Noman\Inventory\Domain\Shared\Enums\ValuationMethod;
use Noman\Inventory\Domain\Shared\ValueObjects\Money;
use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;

/**
 * Weighted Average Cost (WAC) valuation implementation.
 *
 * On each inbound movement, the running weighted average unit cost is
 * recalculated using:
 *
 *   new_avg = (current_value + incoming_quantity * incoming_unit_cost)
 *             / (current_quantity + incoming_quantity)
 *
 * Outbound movements are costed at the current average unit cost.
 *
 * Phase 4 will complete the DB queries for fetching current balances
 * and persisting valuation entries.
 */
final class WeightedAverageValuator implements StockValuatorContract
{
    public function calculateUnitCost(
        string $itemId,
        Quantity $quantity,
        MovementType $movementType,
        ValuationMethod $method,
        ?Money $inboundCost = null,
        ?string $batchId = null,
        ?string $tenantId = null,
    ): Money {
        $currency = config('inventory.currency', 'USD');

        if ($movementType->isInbound()) {
            // For inbound movements, the cost is provided by the source document.
            // If no cost is provided (e.g. opening balance with no cost), default to zero.
            return $inboundCost ?? Money::zero($currency);
        }

        // For outbound movements, use the current weighted average.
        // TODO (Phase 4): Query the current weighted average from
        //   inventory_valuation_entries or inventory_stock_balances for this item/tenant.

        return Money::zero($currency);
    }

    public function recordValuationEntry(
        string $documentLineId,
        string $itemId,
        Quantity $quantity,
        Money $unitCost,
        MovementType $movementType,
        ValuationMethod $method,
        ?string $batchId = null,
        ?string $tenantId = null,
    ): ValuationEntryDTO {
        $totalCost = $unitCost->multiply(
            $movementType->isInbound() ? $quantity->getValue() : -$quantity->getValue()
        );

        // TODO (Phase 4): Persist to inventory_valuation_entries table via Eloquent model.

        $id = (string) Str::ulid();

        return new ValuationEntryDTO(
            id:               $id,
            documentLineId:   $documentLineId,
            itemId:           $itemId,
            quantity:         $quantity,
            unitCost:         $unitCost,
            totalCost:        $totalCost,
            movementType:     $movementType,
            valuationMethod:  $method,
            batchId:          $batchId,
            tenantId:         $tenantId,
        );
    }
}
