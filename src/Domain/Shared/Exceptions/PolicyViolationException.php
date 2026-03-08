<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Shared\Exceptions;

/**
 * Thrown when a stock operation violates a configured inventory policy rule.
 *
 * Examples:
 *  - Batch code not provided when batch_required = true
 *  - Expiry date not provided when expiry_required = true
 *  - Serial number not provided when serial_required = true
 *  - Location not provided when location_required = true
 */
class PolicyViolationException extends InventoryException
{
    public function __construct(
        public readonly string $rule,
        string $message = '',
    ) {
        parent::__construct($message ?: "Inventory policy violation: [{$rule}].");
    }

    public static function batchRequired(string $itemId): self
    {
        return new self('batch_required', "Item '{$itemId}' requires a batch/lot code.");
    }

    public static function expiryRequired(string $itemId): self
    {
        return new self('expiry_required', "Item '{$itemId}' requires an expiry date.");
    }

    public static function serialRequired(string $itemId): self
    {
        return new self('serial_required', "Item '{$itemId}' requires a serial number.");
    }

    public static function locationRequired(string $itemId): self
    {
        return new self('location_required', "Item '{$itemId}' requires a warehouse/location.");
    }

    public static function negativeStockDisallowed(string $itemId): self
    {
        return new self('allow_negative_stock', "Negative stock is not allowed for item '{$itemId}'.");
    }

    public static function approvalRequired(string $documentType): self
    {
        return new self('approval_required', "Document type '{$documentType}' requires approval before posting.");
    }
}
