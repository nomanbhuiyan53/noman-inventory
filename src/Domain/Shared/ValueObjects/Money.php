<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Shared\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * Immutable value object representing a monetary amount with currency.
 *
 * Uses float arithmetic; for accounting-critical use-cases the host application
 * may swap in a Money library (e.g. moneyphp/money) by extending this class or
 * providing a custom StockValuatorContract implementation.
 */
final class Money implements Stringable
{
    private readonly float $amount;
    private readonly string $currency;

    public function __construct(float|int|string $amount, string $currency = 'USD')
    {
        $this->amount   = round((float) $amount, 6);
        $this->currency = strtoupper(trim($currency));

        if (strlen($this->currency) !== 3) {
            throw new InvalidArgumentException(
                "Currency must be a 3-character ISO 4217 code; got '{$this->currency}'."
            );
        }
    }

    // -------------------------------------------------------------------------
    // Factory methods
    // -------------------------------------------------------------------------

    public static function zero(string $currency = 'USD'): self
    {
        return new self(0.0, $currency);
    }

    public static function of(float|int|string $amount, string $currency = 'USD'): self
    {
        return new self($amount, $currency);
    }

    // -------------------------------------------------------------------------
    // Arithmetic – all return new immutable instances
    // -------------------------------------------------------------------------

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amount - $other->amount, $this->currency);
    }

    public function multiply(float|int $factor): self
    {
        return new self($this->amount * (float) $factor, $this->currency);
    }

    public function divide(float|int $divisor): self
    {
        if (abs((float) $divisor) < PHP_FLOAT_EPSILON) {
            throw new InvalidArgumentException('Cannot divide Money by zero.');
        }

        return new self($this->amount / (float) $divisor, $this->currency);
    }

    public function abs(): self
    {
        return new self(abs($this->amount), $this->currency);
    }

    // -------------------------------------------------------------------------
    // Comparison
    // -------------------------------------------------------------------------

    public function equals(self $other): bool
    {
        return $this->currency === $other->currency
            && abs($this->amount - $other->amount) < PHP_FLOAT_EPSILON;
    }

    public function greaterThan(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->amount > $other->amount + PHP_FLOAT_EPSILON;
    }

    public function lessThan(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->amount < $other->amount - PHP_FLOAT_EPSILON;
    }

    // -------------------------------------------------------------------------
    // State checks
    // -------------------------------------------------------------------------

    public function isZero(): bool
    {
        return abs($this->amount) < PHP_FLOAT_EPSILON;
    }

    public function isPositive(): bool
    {
        return $this->amount > PHP_FLOAT_EPSILON;
    }

    public function isNegative(): bool
    {
        return $this->amount < -PHP_FLOAT_EPSILON;
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Returns the amount rounded to standard 2 decimal places for display.
     */
    public function getDisplayAmount(): float
    {
        return round($this->amount, 2);
    }

    public function __toString(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Currency mismatch: cannot operate on {$this->currency} and {$other->currency}."
            );
        }
    }
}
