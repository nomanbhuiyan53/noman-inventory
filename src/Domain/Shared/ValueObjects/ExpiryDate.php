<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Shared\ValueObjects;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use Stringable;

/**
 * Immutable value object representing an item or batch expiry date.
 *
 * Wraps a DateTimeImmutable to provide domain-meaningful helper methods
 * such as isExpired(), isExpiringSoon(), and daysUntilExpiry().
 */
final class ExpiryDate implements Stringable
{
    private readonly DateTimeImmutable $date;

    public function __construct(DateTimeImmutable $date)
    {
        // Normalise to start-of-day so comparisons are date-based, not datetime-based
        $this->date = DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $date->format('Y-m-d') . ' 00:00:00',
            $date->getTimezone()
        ) ?: $date;
    }

    // -------------------------------------------------------------------------
    // Factory methods
    // -------------------------------------------------------------------------

    /**
     * @throws InvalidArgumentException if the string is not a parsable date
     */
    public static function fromString(string $date): self
    {
        $dt = DateTimeImmutable::createFromFormat('Y-m-d', $date);

        if ($dt === false) {
            $dt = new DateTimeImmutable($date);
        }

        return new self($dt);
    }

    public static function fromTimestamp(int $timestamp): self
    {
        return new self((new DateTimeImmutable())->setTimestamp($timestamp));
    }

    public static function fromDateTimeInterface(DateTimeInterface $dt): self
    {
        return new self(DateTimeImmutable::createFromInterface($dt));
    }

    // -------------------------------------------------------------------------
    // Domain logic
    // -------------------------------------------------------------------------

    /**
     * Returns true if the expiry date has already passed (as of today).
     */
    public function isExpired(): bool
    {
        return $this->date < new DateTimeImmutable('today');
    }

    /**
     * Returns true if the expiry date will pass within the given number of days.
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        if ($this->isExpired()) {
            return true;
        }

        $threshold = new DateTimeImmutable("+{$days} days midnight");

        return $this->date <= $threshold;
    }

    /**
     * Returns the number of days remaining until expiry.
     * Negative if already expired.
     */
    public function daysUntilExpiry(): int
    {
        $now  = new DateTimeImmutable('today');
        $diff = $now->diff($this->date);

        return $diff->invert === 1 ? -$diff->days : $diff->days;
    }

    // -------------------------------------------------------------------------
    // Comparison
    // -------------------------------------------------------------------------

    public function equals(self $other): bool
    {
        return $this->date == $other->date;
    }

    public function isBefore(self $other): bool
    {
        return $this->date < $other->date;
    }

    public function isAfter(self $other): bool
    {
        return $this->date > $other->date;
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function toDateTimeImmutable(): DateTimeImmutable
    {
        return $this->date;
    }

    public function toDateString(): string
    {
        return $this->date->format('Y-m-d');
    }

    public function __toString(): string
    {
        return $this->toDateString();
    }
}
