<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Shared\Exceptions;

use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;

/**
 * Thrown when a requested quantity cannot be fulfilled from available stock
 * and negative stock is disallowed by the item's policy.
 */
class InsufficientStockException extends InventoryException
{
    public function __construct(
        public readonly string $itemId,
        public readonly Quantity $requested,
        public readonly Quantity $available,
        string $message = '',
    ) {
        parent::__construct(
            $message ?: sprintf(
                "Insufficient stock for item '%s': requested %s, available %s.",
                $itemId,
                $requested,
                $available
            )
        );
    }

    public static function forItem(string $itemId, Quantity $requested, Quantity $available): self
    {
        return new self($itemId, $requested, $available);
    }
}
