<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Shared\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * Immutable value object representing an item serial number.
 *
 * Serial numbers uniquely identify an individual unit of an item.
 * Used for serialized equipment, high-value assets, and medical devices.
 * Within a given item + warehouse context, a serial number must be unique.
 */
final class SerialCode implements Stringable
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidArgumentException('Serial code cannot be empty.');
        }

        if (strlen($value) > 150) {
            throw new InvalidArgumentException('Serial code cannot exceed 150 characters.');
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
