<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Shared\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * Immutable value object representing a unique stock document number.
 *
 * Document numbers are human-readable identifiers for stock documents
 * such as GRNs, DOs, Transfer Orders, and Stock Adjustments.
 * Example formats: GRN-20241201-0001, ADJ-20241201-A3F2
 */
final class DocumentNumber implements Stringable
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $value = strtoupper(trim($value));

        if ($value === '') {
            throw new InvalidArgumentException('Document number cannot be empty.');
        }

        if (strlen($value) > 64) {
            throw new InvalidArgumentException('Document number cannot exceed 64 characters.');
        }

        $this->value = $value;
    }

    public static function of(string $value): self
    {
        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
