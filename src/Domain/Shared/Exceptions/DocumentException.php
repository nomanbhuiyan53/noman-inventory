<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Shared\Exceptions;

/**
 * Thrown for invalid document state transitions or posting violations.
 */
class DocumentException extends InventoryException
{
    public static function alreadyPosted(string $documentNumber): self
    {
        return new self("Document '{$documentNumber}' has already been posted.");
    }

    public static function cannotPost(string $documentNumber, string $currentStatus): self
    {
        return new self(
            "Document '{$documentNumber}' cannot be posted in status '{$currentStatus}'."
        );
    }

    public static function cannotReverse(string $documentNumber, string $currentStatus): self
    {
        return new self(
            "Document '{$documentNumber}' cannot be reversed in status '{$currentStatus}'. Only posted documents can be reversed."
        );
    }

    public static function notFound(string $documentNumber): self
    {
        return new self("Document '{$documentNumber}' not found.");
    }

    public static function alreadyReversed(string $documentNumber): self
    {
        return new self("Document '{$documentNumber}' has already been reversed.");
    }

    public static function idempotencyConflict(string $idempotencyKey): self
    {
        return new self(
            "A document with idempotency key '{$idempotencyKey}' has already been processed."
        );
    }
}
