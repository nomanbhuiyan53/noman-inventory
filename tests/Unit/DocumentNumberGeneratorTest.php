<?php

declare(strict_types=1);

use Noman\Inventory\Infrastructure\Support\DefaultDocumentNumberGenerator;

describe('DefaultDocumentNumberGenerator', function () {

    it('generates a non-empty document number', function () {
        $generator = new DefaultDocumentNumberGenerator();
        $number    = $generator->generate('receive');

        expect((string) $number)->not->toBeEmpty();
    });

    it('generates GRN prefix for receive type', function () {
        $generator = new DefaultDocumentNumberGenerator();
        $number    = $generator->generate('receive');

        expect((string) $number)->toStartWith('GRN-');
    });

    it('generates ADJ prefix for adjustment type', function () {
        $generator = new DefaultDocumentNumberGenerator();
        $number    = $generator->generate('adjustment');

        expect((string) $number)->toStartWith('ADJ-');
    });

    it('generates REV prefix for reversal type', function () {
        $generator = new DefaultDocumentNumberGenerator();
        $number    = $generator->generate('reversal');

        expect((string) $number)->toStartWith('REV-');
    });

    it('includes tenant segment when tenant ID is provided', function () {
        $generator = new DefaultDocumentNumberGenerator();
        $number    = $generator->generate('receive', 'TENANT1');

        expect((string) $number)->toContain('TENA');
    });

    it('generates unique numbers across calls', function () {
        $generator = new DefaultDocumentNumberGenerator();
        $a         = (string) $generator->generate('receive');
        $b         = (string) $generator->generate('receive');

        expect($a)->not->toBe($b);
    });
});
