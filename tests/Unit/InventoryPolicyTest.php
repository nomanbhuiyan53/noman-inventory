<?php

declare(strict_types=1);

use Noman\Inventory\Domain\Shared\Enums\AllocationStrategy;
use Noman\Inventory\Domain\Shared\Enums\IndustryProfile;
use Noman\Inventory\Domain\Shared\Enums\ValuationMethod;
use Noman\Inventory\Domain\Shared\ValueObjects\InventoryPolicy;

describe('InventoryPolicy value object', function () {

    it('creates from array with defaults', function () {
        $policy = InventoryPolicy::fromArray([]);

        expect($policy->allowNegativeStock)->toBeFalse();
        expect($policy->batchRequired)->toBeFalse();
        expect($policy->expiryRequired)->toBeFalse();
        expect($policy->serialRequired)->toBeFalse();
        expect($policy->locationRequired)->toBeFalse();
        expect($policy->approvalRequired)->toBeFalse();
        expect($policy->allocationStrategy)->toBe(AllocationStrategy::Fefo);
        expect($policy->valuationMethod)->toBe(ValuationMethod::WeightedAverage);
    });

    it('creates from industry profile (pharma)', function () {
        $policy = InventoryPolicy::fromProfile(IndustryProfile::PharmaGoods);

        expect($policy->batchRequired)->toBeTrue();
        expect($policy->expiryRequired)->toBeTrue();
        expect($policy->locationRequired)->toBeTrue();
        expect($policy->allowNegativeStock)->toBeFalse();
        expect($policy->allocationStrategy)->toBe(AllocationStrategy::Fefo);
        expect($policy->valuationMethod)->toBe(ValuationMethod::Fifo);
    });

    it('creates from industry profile (standard goods)', function () {
        $policy = InventoryPolicy::fromProfile(IndustryProfile::StandardGoods);

        expect($policy->batchRequired)->toBeFalse();
        expect($policy->expiryRequired)->toBeFalse();
        expect($policy->allocationStrategy)->toBe(AllocationStrategy::Fifo);
    });

    it('creates from industry profile (serialized equipment)', function () {
        $policy = InventoryPolicy::fromProfile(IndustryProfile::SerializedEquipment);

        expect($policy->serialRequired)->toBeTrue();
        expect($policy->allocationStrategy)->toBe(AllocationStrategy::Manual);
    });

    it('merges overrides on top of existing policy', function () {
        $base = InventoryPolicy::fromProfile(IndustryProfile::StandardGoods);

        $merged = $base->merge([
            'allow_negative_stock' => true,
            'batch_required'       => true,
        ]);

        expect($merged->allowNegativeStock)->toBeTrue();
        expect($merged->batchRequired)->toBeTrue();
        // Original values preserved
        expect($merged->serialRequired)->toBeFalse();
    });

    it('serialises to and from array', function () {
        $policy = InventoryPolicy::fromProfile(IndustryProfile::PharmaGoods);
        $array  = $policy->toArray();
        $restored = InventoryPolicy::fromArray($array);

        expect($restored->batchRequired)->toBeTrue();
        expect($restored->expiryRequired)->toBeTrue();
        expect($restored->allocationStrategy)->toBe(AllocationStrategy::Fefo);
    });
});
