<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Shared\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * Immutable value object representing a Stock Keeping Unit code.
 *
 * SKUs are normalised to uppercase and must contain only alphanumeric
 * characters, hyphens, underscores, and dots, with a maximum of 100 chars.
 */
final class Sku implements Stringable
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $normalised = strtoupper(trim($value));

        if ($normalised === '') {
            throw new InvalidArgumentException('SKU cannot be empty.');
        }

        if (strlen($normalised) > 100) {
            throw new InvalidArgumentException('SKU cannot exceed 100 characters.');
        }

        if (! preg_match('/^[A-Z0-9\-_\.]+$/', $normalised)) {
            throw new InvalidArgumentException(
                "SKU '{$normalised}' contains invalid characters. "
                . 'Only A-Z, 0-9, hyphens, underscores, and dots are allowed.'
            );
        }

        $this->value = $normalised;
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
