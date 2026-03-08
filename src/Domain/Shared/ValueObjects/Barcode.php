<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Shared\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * Immutable value object representing a product barcode.
 *
 * Supports common barcode formats: EAN-8, EAN-13, UPC-A, UPC-E, Code-128,
 * Code-39, QR, Data Matrix, and free-form internal codes.
 * Validation is intentionally permissive to support custom internal barcodes.
 */
final class Barcode implements Stringable
{
    private readonly string $value;
    private readonly string $type;

    public function __construct(string $value, string $type = 'auto')
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidArgumentException('Barcode value cannot be empty.');
        }

        if (strlen($value) > 255) {
            throw new InvalidArgumentException('Barcode value cannot exceed 255 characters.');
        }

        $this->value = $value;
        $this->type  = strtolower($type);
    }

    public static function of(string $value, string $type = 'auto'): self
    {
        return new self($value, $type);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Checks if this looks like a valid EAN-13 barcode (numeric, 13 digits, valid check digit).
     */
    public function isValidEan13(): bool
    {
        if (! preg_match('/^\d{13}$/', $this->value)) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int) $this->value[$i];
            $sum  += ($i % 2 === 0) ? $digit : $digit * 3;
        }

        $checkDigit = (10 - ($sum % 10)) % 10;

        return $checkDigit === (int) $this->value[12];
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
