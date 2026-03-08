<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Shared\Exceptions;

use RuntimeException;

/**
 * Base exception for all domain exceptions thrown by the noman-inventory package.
 *
 * All specific inventory exceptions extend this class, allowing host applications
 * to catch all inventory errors with a single catch block if needed.
 */
class InventoryException extends RuntimeException
{
    public static function because(string $message): static
    {
        return new static($message);
    }
}
