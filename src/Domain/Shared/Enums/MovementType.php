<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Shared\Enums;

/**
 * Represents every possible type of inventory movement in the append-only ledger.
 *
 * Movements that increase stock (in-bound) are considered positive.
 * Movements that decrease stock (out-bound) are considered negative.
 * Each movement type is tied to specific document types and business rules.
 */
enum MovementType: string
{
    /** Opening balance entry when initialising stock for the first time */
    case Opening            = 'opening';

    /** Stock received from a purchase/vendor order */
    case PurchaseIn         = 'purchase_in';

    /** Stock issued/sold to a customer */
    case SaleOut            = 'sale_out';

    /** Stock received at destination location during an inter-warehouse transfer */
    case TransferIn         = 'transfer_in';

    /** Stock dispatched from source location during an inter-warehouse transfer */
    case TransferOut        = 'transfer_out';

    /** Manual positive adjustment (e.g. found extra stock during count) */
    case AdjustmentIn       = 'adjustment_in';

    /** Manual negative adjustment (e.g. discrepancy found during count) */
    case AdjustmentOut      = 'adjustment_out';

    /** Goods produced in-house and added to stock */
    case ProductionIn       = 'production_in';

    /** Raw materials consumed in a production run */
    case ConsumptionOut     = 'consumption_out';

    /** Items returned by customer and put back in stock */
    case ReturnIn           = 'return_in';

    /** Items returned to vendor / supplier */
    case ReturnOut          = 'return_out';

    /** Stock written off due to damage, spoilage, or loss */
    case WastageOut         = 'wastage_out';

    /** Stock classified as dead / obsolete and removed */
    case DeadStockOut       = 'dead_stock_out';

    /** Expired items removed from available stock */
    case ExpiredOut         = 'expired_out';

    /** Items moved into quarantine / hold area */
    case QuarantineIn       = 'quarantine_in';

    /** Items released from quarantine back to available stock */
    case QuarantineOut      = 'quarantine_out';

    /**
     * Returns true if this movement type increases available stock.
     */
    public function isInbound(): bool
    {
        return match ($this) {
            self::Opening,
            self::PurchaseIn,
            self::TransferIn,
            self::AdjustmentIn,
            self::ProductionIn,
            self::ReturnIn,
            self::QuarantineOut => true,
            default             => false,
        };
    }

    /**
     * Returns true if this movement type decreases available stock.
     */
    public function isOutbound(): bool
    {
        return ! $this->isInbound();
    }

    /**
     * Returns +1 for inbound movements and -1 for outbound movements.
     * Used when calculating net quantity impact on the ledger.
     */
    public function directionMultiplier(): int
    {
        return $this->isInbound() ? 1 : -1;
    }

    /**
     * Human-readable label for reporting and UI display.
     */
    public function label(): string
    {
        return match ($this) {
            self::Opening         => 'Opening Balance',
            self::PurchaseIn      => 'Purchase In',
            self::SaleOut         => 'Sale Out',
            self::TransferIn      => 'Transfer In',
            self::TransferOut     => 'Transfer Out',
            self::AdjustmentIn    => 'Adjustment In',
            self::AdjustmentOut   => 'Adjustment Out',
            self::ProductionIn    => 'Production In',
            self::ConsumptionOut  => 'Consumption Out',
            self::ReturnIn        => 'Return In',
            self::ReturnOut       => 'Return Out',
            self::WastageOut      => 'Wastage Out',
            self::DeadStockOut    => 'Dead Stock Out',
            self::ExpiredOut      => 'Expired Out',
            self::QuarantineIn    => 'Quarantine In',
            self::QuarantineOut   => 'Quarantine Out',
        };
    }
}
