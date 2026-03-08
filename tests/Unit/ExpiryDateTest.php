<?php

declare(strict_types=1);

use Noman\Inventory\Domain\Shared\ValueObjects\ExpiryDate;

describe('ExpiryDate value object', function () {

    it('creates from string', function () {
        $date = ExpiryDate::fromString('2030-06-01');

        expect($date->toDateString())->toBe('2030-06-01');
        expect($date->isExpired())->toBeFalse();
    });

    it('detects expired date', function () {
        $past = ExpiryDate::fromString('2000-01-01');

        expect($past->isExpired())->toBeTrue();
    });

    it('detects expiring soon', function () {
        $soon = ExpiryDate::fromString(now()->addDays(10)->format('Y-m-d'));

        expect($soon->isExpiringSoon(30))->toBeTrue();
        expect($soon->isExpiringSoon(5))->toBeFalse();
    });

    it('calculates days until expiry', function () {
        $future = ExpiryDate::fromString(now()->addDays(15)->format('Y-m-d'));

        expect($future->daysUntilExpiry())->toBeGreaterThanOrEqual(14);
        expect($future->daysUntilExpiry())->toBeLessThanOrEqual(16);
    });

    it('returns negative days for expired date', function () {
        $past = ExpiryDate::fromString(now()->subDays(5)->format('Y-m-d'));

        expect($past->daysUntilExpiry())->toBeLessThan(0);
    });

    it('compares two expiry dates', function () {
        $earlier = ExpiryDate::fromString('2025-01-01');
        $later   = ExpiryDate::fromString('2025-12-31');

        expect($earlier->isBefore($later))->toBeTrue();
        expect($later->isAfter($earlier))->toBeTrue();
        expect($earlier->equals($later))->toBeFalse();
    });
});
