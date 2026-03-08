<?php

declare(strict_types=1);

use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;

describe('Quantity value object', function () {

    it('creates a zero quantity', function () {
        $qty = Quantity::zero();

        expect($qty->isZero())->toBeTrue();
        expect($qty->getValue())->toBe(0.0);
    });

    it('creates from float', function () {
        $qty = Quantity::of(10.5);

        expect($qty->getValue())->toBe(10.5);
    });

    it('adds two quantities', function () {
        $a = Quantity::of(10);
        $b = Quantity::of(5);

        expect($a->add($b)->getValue())->toBe(15.0);
    });

    it('subtracts two quantities', function () {
        $a = Quantity::of(10);
        $b = Quantity::of(3);

        expect($a->subtract($b)->getValue())->toBe(7.0);
    });

    it('multiplies a quantity', function () {
        $qty = Quantity::of(4);

        expect($qty->multiply(3)->getValue())->toBe(12.0);
    });

    it('divides a quantity', function () {
        $qty = Quantity::of(10);

        expect($qty->divide(4)->getValue())->toBe(2.5);
    });

    it('throws when dividing by zero', function () {
        Quantity::of(10)->divide(0);
    })->throws(InvalidArgumentException::class);

    it('correctly identifies positive', function () {
        expect(Quantity::of(0.001)->isPositive())->toBeTrue();
        expect(Quantity::of(0)->isPositive())->toBeFalse();
        expect(Quantity::of(-1)->isPositive())->toBeFalse();
    });

    it('correctly identifies negative', function () {
        expect(Quantity::of(-1)->isNegative())->toBeTrue();
        expect(Quantity::of(0)->isNegative())->toBeFalse();
    });

    it('compares quantities with greaterThan', function () {
        $a = Quantity::of(10);
        $b = Quantity::of(5);

        expect($a->greaterThan($b))->toBeTrue();
        expect($b->greaterThan($a))->toBeFalse();
    });

    it('returns absolute value', function () {
        expect(Quantity::of(-5)->abs()->getValue())->toBe(5.0);
        expect(Quantity::of(5)->abs()->getValue())->toBe(5.0);
    });

    it('negates a quantity', function () {
        expect(Quantity::of(7)->negate()->getValue())->toBe(-7.0);
    });

    it('is immutable — operations return new instances', function () {
        $original = Quantity::of(10);
        $result   = $original->add(Quantity::of(5));

        expect($original->getValue())->toBe(10.0);
        expect($result->getValue())->toBe(15.0);
    });
});
