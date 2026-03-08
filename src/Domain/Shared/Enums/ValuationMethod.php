<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Shared\Enums;

/**
 * Inventory costing / valuation method applied when posting stock movements.
 *
 * The method determines how the cost of goods sold (COGS) and closing
 * inventory value are calculated.
 */
enum ValuationMethod: string
{
    /**
     * First In, First Out.
     * The cost of the earliest purchased units is used first when goods leave stock.
     * Results in most accurate COGS during inflationary periods.
     * Requires per-batch cost layer tracking.
     */
    case Fifo               = 'fifo';

    /**
     * Weighted Average Cost.
     * Running average cost per unit is recalculated after each receipt.
     * COGS is always at the current average cost.
     * Simpler to maintain; suitable for commodities and bulk goods.
     */
    case WeightedAverage    = 'weighted_average';

    /**
     * Standard Cost.
     * A predetermined standard cost per unit is used for all valuations.
     * Variances between standard and actual cost are tracked separately.
     * Used in manufacturing and cost-controlled environments.
     */
    case StandardCost       = 'standard_cost';

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Fifo            => 'FIFO (First In, First Out)',
            self::WeightedAverage => 'Weighted Average Cost',
            self::StandardCost    => 'Standard Cost',
        };
    }

    /**
     * Returns true if this method requires per-receipt cost layer records.
     */
    public function requiresCostLayers(): bool
    {
        return $this === self::Fifo;
    }
}
