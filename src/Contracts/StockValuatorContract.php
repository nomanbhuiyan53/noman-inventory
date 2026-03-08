<?php

declare(strict_types=1);

namespace Noman\Inventory\Contracts;

use Noman\Inventory\Application\DTOs\ValuationEntryDTO;
use Noman\Inventory\Domain\Shared\Enums\MovementType;
use Noman\Inventory\Domain\Shared\Enums\ValuationMethod;
use Noman\Inventory\Domain\Shared\ValueObjects\Money;
use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;

/**
 * Contract for the stock valuation engine.
 *
 * Responsible for:
 *  1. Computing the unit cost at the time a movement is posted
 *     (using FIFO layers, weighted average, or standard cost).
 *  2. Persisting valuation entries for each stock movement line.
 *
 * The valuation engine ensures every stock movement has an associated
 * monetary value, enabling accurate COGS and inventory balance reporting.
 */
interface StockValuatorContract
{
    /**
     * Calculate the unit cost for a stock movement.
     *
     * For outbound movements, the cost is determined by the valuation method:
     *   - FIFO: cost of the earliest unconsumed receipt layer
     *   - Weighted Average: current running average cost
     *   - Standard Cost: the item's pre-configured standard cost
     *
     * For inbound movements, the cost is typically passed from the source document
     * (purchase order price, production cost, etc.).
     *
     * @param  string          $itemId        Inventory item identifier
     * @param  Quantity        $quantity      Quantity being moved
     * @param  MovementType    $movementType  Direction and nature of movement
     * @param  ValuationMethod $method        Costing method to apply
     * @param  Money|null      $inboundCost   Unit cost for inbound movements (purchase price)
     * @param  string|null     $batchId       Batch identifier for batch-specific costing
     * @param  string|null     $tenantId      Tenant scope
     * @return Money                          Computed unit cost per unit
     */
    public function calculateUnitCost(
        string $itemId,
        Quantity $quantity,
        MovementType $movementType,
        ValuationMethod $method,
        ?Money $inboundCost = null,
        ?string $batchId = null,
        ?string $tenantId = null,
    ): Money;

    /**
     * Build and persist a valuation entry for a posted document line.
     *
     * @param  string          $documentLineId  The stock document line ID
     * @param  string          $itemId          Inventory item identifier
     * @param  Quantity        $quantity        Quantity of the movement
     * @param  Money           $unitCost        Resolved unit cost
     * @param  MovementType    $movementType    Movement type
     * @param  ValuationMethod $method          Costing method used
     * @param  string|null     $batchId         Batch identifier
     * @param  string|null     $tenantId        Tenant scope
     * @return ValuationEntryDTO                The created valuation entry
     */
    public function recordValuationEntry(
        string $documentLineId,
        string $itemId,
        Quantity $quantity,
        Money $unitCost,
        MovementType $movementType,
        ValuationMethod $method,
        ?string $batchId = null,
        ?string $tenantId = null,
    ): ValuationEntryDTO;
}
