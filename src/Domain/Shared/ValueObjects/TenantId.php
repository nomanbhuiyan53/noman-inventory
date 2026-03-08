<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Shared\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * Immutable value object representing a tenant identifier.
 *
 * The format of the tenant ID is deliberately open: it may be a UUID,
 * a ULID, an integer string, or any other stable unique identifier
 * that the host application uses to distinguish tenants.
 *
 * All inventory services read the current tenant through TenantResolverContract
 * which returns an instance of this class (or null in single-tenant mode).
 */
final class TenantId implements Stringable
{
    private readonly string $value;

    public function __construct(string|int $value)
    {
        $value = (string) $value;

        if (trim($value) === '') {
            throw new InvalidArgumentException('TenantId cannot be empty.');
        }

        if (strlen($value) > 64) {
            throw new InvalidArgumentException('TenantId cannot exceed 64 characters.');
        }

        $this->value = $value;
    }

    public static function of(string|int $value): self
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
