<?php

declare(strict_types=1);

use Noman\Inventory\Domain\Shared\ValueObjects\Money;

describe('Money value object', function () {

    it('creates a zero money', function () {
        $m = Money::zero('USD');

        expect($m->isZero())->toBeTrue();
        expect($m->getCurrency())->toBe('USD');
    });

    it('adds two money objects of same currency', function () {
        $a = Money::of(100, 'USD');
        $b = Money::of(50.5, 'USD');

        expect($a->add($b)->getAmount())->toBe(150.5);
    });

    it('throws when adding different currencies', function () {
        Money::of(100, 'USD')->add(Money::of(50, 'EUR'));
    })->throws(InvalidArgumentException::class);

    it('multiplies by a factor', function () {
        $m = Money::of(10, 'USD');

        expect($m->multiply(3)->getAmount())->toBe(30.0);
    });

    it('detects positive', function () {
        expect(Money::of(0.01, 'USD')->isPositive())->toBeTrue();
        expect(Money::of(0, 'USD')->isPositive())->toBeFalse();
    });

    it('rejects invalid currency code', function () {
        new Money(100, 'US');
    })->throws(InvalidArgumentException::class);

    it('is immutable', function () {
        $original = Money::of(100, 'USD');
        $result   = $original->add(Money::of(50, 'USD'));

        expect($original->getAmount())->toBe(100.0);
        expect($result->getAmount())->toBe(150.0);
    });
});
