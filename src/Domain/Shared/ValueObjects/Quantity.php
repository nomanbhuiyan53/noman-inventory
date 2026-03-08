<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Shared\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * Immutable value object representing a stock quantity.
 *
 * Quantities can be positive, zero, or negative (when the policy allows).
 * All arithmetic operations return new instances (immutability preserved).
 * Comparison uses PHP_FLOAT_EPSILON to avoid floating-point drift.
 */
final class Quantity implements Stringable
{
    private readonly float $value;

    public function __construct(float|int|string $value)
    {
        $this->value = (float) $value;
    }

    // -------------------------------------------------------------------------
    // Factory methods
    // -------------------------------------------------------------------------

    public static function zero(): self
    {
        return new self(0.0);
    }

    public static function of(float|int|string $value): self
    {
        return new self($value);
    }

    // -------------------------------------------------------------------------
    // Arithmetic – all return new immutable instances
    // -------------------------------------------------------------------------

    public function add(self $other): self
    {
        return new self($this->value + $other->value);
    }

    public function subtract(self $other): self
    {
        return new self($this->value - $other->value);
    }

    public function multiply(float|int $factor): self
    {
        return new self($this->value * (float) $factor);
    }

    public function divide(float|int $divisor): self
    {
        if (abs((float) $divisor) < PHP_FLOAT_EPSILON) {
            throw new InvalidArgumentException('Cannot divide a Quantity by zero.');
        }

        return new self($this->value / (float) $divisor);
    }

    public function abs(): self
    {
        return new self(abs($this->value));
    }

    public function negate(): self
    {
        return new self(-$this->value);
    }

    // -------------------------------------------------------------------------
    // Comparison
    // -------------------------------------------------------------------------

    public function equals(self $other): bool
    {
        return abs($this->value - $other->value) < PHP_FLOAT_EPSILON;
    }

    public function greaterThan(self $other): bool
    {
        return $this->value > $other->value + PHP_FLOAT_EPSILON;
    }

    public function greaterThanOrEqual(self $other): bool
    {
        return $this->greaterThan($other) || $this->equals($other);
    }

    public function lessThan(self $other): bool
    {
        return $this->value < $other->value - PHP_FLOAT_EPSILON;
    }

    public function lessThanOrEqual(self $other): bool
    {
        return $this->lessThan($other) || $this->equals($other);
    }

    // -------------------------------------------------------------------------
    // State checks
    // -------------------------------------------------------------------------

    public function isZero(): bool
    {
        return abs($this->value) < PHP_FLOAT_EPSILON;
    }

    public function isPositive(): bool
    {
        return $this->value > PHP_FLOAT_EPSILON;
    }

    public function isNegative(): bool
    {
        return $this->value < -PHP_FLOAT_EPSILON;
    }

    public function isPositiveOrZero(): bool
    {
        return ! $this->isNegative();
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function getValue(): float
    {
        return $this->value;
    }

    public function toFloat(): float
    {
        return $this->value;
    }

    public function toInt(): int
    {
        return (int) round($this->value);
    }

    public function __toString(): string
    {
        // Strip unnecessary trailing zeros while keeping precision
        return rtrim(rtrim(number_format($this->value, 6, '.', ''), '0'), '.');
    }
}
