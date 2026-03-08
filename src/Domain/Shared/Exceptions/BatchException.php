<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Shared\Exceptions;

/**
 * Thrown for batch/lot related domain violations.
 */
class BatchException extends InventoryException
{
    public static function expired(string $batchCode, string $itemId): self
    {
        return new self("Batch '{$batchCode}' for item '{$itemId}' has expired and cannot be used.");
    }

    public static function notFound(string $batchCode, string $itemId): self
    {
        return new self("Batch '{$batchCode}' for item '{$itemId}' was not found.");
    }

    public static function duplicateSerialInBatch(string $serial, string $batchCode): self
    {
        return new self("Serial number '{$serial}' already exists in batch '{$batchCode}'.");
    }
}
