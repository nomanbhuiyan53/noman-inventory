<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Shared\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * Immutable value object representing a warehouse location / bin code.
 *
 * Location codes are used to identify specific physical areas within a
 * warehouse: zones, aisles, racks, shelves, or bins.
 * Example: WH01-A3-R2-S4-B01 (Warehouse 01, Aisle A3, Rack 2, Shelf 4, Bin 01)
 */
final class LocationCode implements Stringable
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $value = strtoupper(trim($value));

        if ($value === '') {
            throw new InvalidArgumentException('Location code cannot be empty.');
        }

        if (strlen($value) > 50) {
            throw new InvalidArgumentException('Location code cannot exceed 50 characters.');
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
