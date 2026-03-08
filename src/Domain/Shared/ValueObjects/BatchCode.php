<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Shared\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * Immutable value object representing a batch/lot code.
 *
 * Batch codes identify a group of items manufactured or received together.
 * They are critical for traceability in pharma, food, and regulated industries.
 */
final class BatchCode implements Stringable
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidArgumentException('Batch code cannot be empty.');
        }

        if (strlen($value) > 100) {
            throw new InvalidArgumentException('Batch code cannot exceed 100 characters.');
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
