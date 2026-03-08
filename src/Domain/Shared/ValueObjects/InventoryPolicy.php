<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Shared\ValueObjects;

use Noman\Inventory\Domain\Shared\Enums\AllocationStrategy;
use Noman\Inventory\Domain\Shared\Enums\IndustryProfile;
use Noman\Inventory\Domain\Shared\Enums\ValuationMethod;

/**
 * Immutable value object representing the resolved inventory policy for an item.
 *
 * Policy is resolved by the PolicyResolverContract in a layered fashion:
 *   1. Global config defaults
 *   2. Industry profile defaults
 *   3. Item-type level overrides
 *   4. Item level overrides
 *
 * The final InventoryPolicy object is passed to Actions and Validators
 * at the time a stock operation is performed.
 */
final class InventoryPolicy
{
    public function __construct(
        /** Whether stock quantity is allowed to go below zero for this item */
        public readonly bool $allowNegativeStock = false,

        /** Whether a batch/lot reference is mandatory for every movement */
        public readonly bool $batchRequired = false,

        /** Whether an expiry date must be present on every batch */
        public readonly bool $expiryRequired = false,

        /** Whether each unit must have a unique serial number */
        public readonly bool $serialRequired = false,

        /** Whether a warehouse/location must be specified on every movement */
        public readonly bool $locationRequired = false,

        /** Whether documents of this item type require approval before posting */
        public readonly bool $approvalRequired = false,

        /** Which allocation strategy to use when issuing or transferring stock */
        public readonly AllocationStrategy $allocationStrategy = AllocationStrategy::Fefo,

        /** Costing method applied when valuation entries are created */
        public readonly ValuationMethod $valuationMethod = ValuationMethod::WeightedAverage,

        /** Minutes after which a reservation for this item automatically expires; null = no expiry */
        public readonly ?int $reservationExpiryMinutes = null,

        /** The industry profile that was used as the base for this policy */
        public readonly IndustryProfile $industryProfile = IndustryProfile::StandardGoods,
    ) {}

    // -------------------------------------------------------------------------
    // Factory methods
    // -------------------------------------------------------------------------

    /**
     * Build a policy from a raw associative array (e.g. from config or DB row).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            allowNegativeStock:       (bool) ($data['allow_negative_stock'] ?? false),
            batchRequired:            (bool) ($data['batch_required'] ?? false),
            expiryRequired:           (bool) ($data['expiry_required'] ?? false),
            serialRequired:           (bool) ($data['serial_required'] ?? false),
            locationRequired:         (bool) ($data['location_required'] ?? false),
            approvalRequired:         (bool) ($data['approval_required'] ?? false),
            allocationStrategy:       AllocationStrategy::from($data['allocation_strategy'] ?? AllocationStrategy::Fefo->value),
            valuationMethod:          ValuationMethod::from($data['valuation_method'] ?? ValuationMethod::WeightedAverage->value),
            reservationExpiryMinutes: isset($data['reservation_expiry_minutes']) ? (int) $data['reservation_expiry_minutes'] : null,
            industryProfile:          IndustryProfile::from($data['industry_profile'] ?? IndustryProfile::StandardGoods->value),
        );
    }

    /**
     * Build a policy from an IndustryProfile's defaults.
     */
    public static function fromProfile(IndustryProfile $profile): self
    {
        return self::fromArray(array_merge(
            $profile->defaultPolicies(),
            ['industry_profile' => $profile->value]
        ));
    }

    /**
     * Returns a new policy that merges the given overrides on top of this policy.
     * Only keys present in the overrides array will be changed.
     */
    public function merge(array $overrides): self
    {
        return self::fromArray(array_merge($this->toArray(), $overrides));
    }

    // -------------------------------------------------------------------------
    // Serialisation
    // -------------------------------------------------------------------------

    public function toArray(): array
    {
        return [
            'allow_negative_stock'      => $this->allowNegativeStock,
            'batch_required'            => $this->batchRequired,
            'expiry_required'           => $this->expiryRequired,
            'serial_required'           => $this->serialRequired,
            'location_required'         => $this->locationRequired,
            'approval_required'         => $this->approvalRequired,
            'allocation_strategy'       => $this->allocationStrategy->value,
            'valuation_method'          => $this->valuationMethod->value,
            'reservation_expiry_minutes'=> $this->reservationExpiryMinutes,
            'industry_profile'          => $this->industryProfile->value,
        ];
    }
}
