<?php

declare(strict_types=1);

use Noman\Inventory\Domain\Shared\Enums\MovementType;

describe('MovementType enum', function () {

    it('correctly identifies inbound movements', function () {
        $inbound = [
            MovementType::Opening,
            MovementType::PurchaseIn,
            MovementType::TransferIn,
            MovementType::AdjustmentIn,
            MovementType::ProductionIn,
            MovementType::ReturnIn,
            MovementType::QuarantineOut,
        ];

        foreach ($inbound as $type) {
            expect($type->isInbound())->toBeTrue(
                "{$type->value} should be inbound"
            );
        }
    });

    it('correctly identifies outbound movements', function () {
        $outbound = [
            MovementType::SaleOut,
            MovementType::TransferOut,
            MovementType::AdjustmentOut,
            MovementType::ConsumptionOut,
            MovementType::ReturnOut,
            MovementType::WastageOut,
            MovementType::DeadStockOut,
            MovementType::ExpiredOut,
            MovementType::QuarantineIn,
        ];

        foreach ($outbound as $type) {
            expect($type->isOutbound())->toBeTrue(
                "{$type->value} should be outbound"
            );
        }
    });

    it('returns +1 multiplier for inbound', function () {
        expect(MovementType::PurchaseIn->directionMultiplier())->toBe(1);
    });

    it('returns -1 multiplier for outbound', function () {
        expect(MovementType::SaleOut->directionMultiplier())->toBe(-1);
    });

    it('has a label for every case', function () {
        foreach (MovementType::cases() as $case) {
            expect($case->label())->toBeString()->not->toBeEmpty();
        }
    });
});
