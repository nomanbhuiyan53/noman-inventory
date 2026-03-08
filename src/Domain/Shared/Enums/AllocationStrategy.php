<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Shared\Enums;

/**
 * Strategy used to select which stock batches/lots are consumed first
 * when issuing, transferring, or reserving stock.
 */
enum AllocationStrategy: string
{
    /**
     * First Expired, First Out.
     * Allocates batches with the earliest expiry date first.
     * Recommended for pharma, food, perishables, and livestock supplies.
     */
    case Fefo   = 'fefo';

    /**
     * First In, First Out.
     * Allocates stock received earliest first, regardless of expiry.
     * Standard approach for most retail and warehouse operations.
     */
    case Fifo   = 'fifo';

    /**
     * Manual allocation.
     * The caller must explicitly specify which batches/serials/locations
     * to consume. No automatic selection is made.
     */
    case Manual = 'manual';

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Fefo   => 'FEFO (First Expired, First Out)',
            self::Fifo   => 'FIFO (First In, First Out)',
            self::Manual => 'Manual Allocation',
        };
    }

    /**
     * Whether this strategy requires expiry dates to be present on batches.
     */
    public function requiresExpiry(): bool
    {
        return $this === self::Fefo;
    }
}
