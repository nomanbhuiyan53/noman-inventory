<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Shared\Enums;

/**
 * Pre-defined industry/commodity profiles that bundle a set of sensible
 * default inventory policies for common business domains.
 *
 * Profiles are applied at item or item-type level, and all settings they
 * produce can be overridden by explicit item-level policy configuration.
 */
enum IndustryProfile: string
{
    /**
     * General merchandise, retail goods, spare parts.
     * Minimal tracking requirements; FIFO allocation; no expiry.
     */
    case StandardGoods          = 'standard_goods';

    /**
     * Pharmaceutical products, medicines, medical consumables.
     * Mandatory batch, expiry, and location tracking.
     * FEFO allocation; strict no-negative-stock enforcement.
     */
    case PharmaGoods            = 'pharma_goods';

    /**
     * Feed, supplements, and supplies used on livestock / animal farms.
     * Batch and expiry tracking; FEFO allocation.
     */
    case LivestockSupply        = 'livestock_supply';

    /**
     * Equipment, machinery, tools tracked individually by serial number.
     * Serial required; no batch expiry; FIFO allocation.
     */
    case SerializedEquipment    = 'serialized_equipment';

    /**
     * Pet food, treats, fresh goods with short shelf-life.
     * Batch and expiry required; FEFO allocation; strict no-negative-stock.
     */
    case PerishablePetFood      = 'perishable_pet_food';

    /**
     * Human-readable label for the profile.
     */
    public function label(): string
    {
        return match ($this) {
            self::StandardGoods       => 'Standard Goods',
            self::PharmaGoods         => 'Pharmaceutical Goods',
            self::LivestockSupply     => 'Livestock Supply',
            self::SerializedEquipment => 'Serialized Equipment',
            self::PerishablePetFood   => 'Perishable Pet Food',
        };
    }

    /**
     * Returns the canonical policy defaults for this industry profile.
     * These defaults are merged with global config and can be overridden
     * at item-type or item level.
     */
    public function defaultPolicies(): array
    {
        return match ($this) {
            self::StandardGoods => [
                'batch_required'       => false,
                'expiry_required'      => false,
                'serial_required'      => false,
                'location_required'    => false,
                'allow_negative_stock' => false,
                'approval_required'    => false,
                'allocation_strategy'  => AllocationStrategy::Fifo->value,
                'valuation_method'     => ValuationMethod::WeightedAverage->value,
            ],
            self::PharmaGoods => [
                'batch_required'       => true,
                'expiry_required'      => true,
                'serial_required'      => false,
                'location_required'    => true,
                'allow_negative_stock' => false,
                'approval_required'    => true,
                'allocation_strategy'  => AllocationStrategy::Fefo->value,
                'valuation_method'     => ValuationMethod::Fifo->value,
            ],
            self::LivestockSupply => [
                'batch_required'       => true,
                'expiry_required'      => true,
                'serial_required'      => false,
                'location_required'    => false,
                'allow_negative_stock' => false,
                'approval_required'    => false,
                'allocation_strategy'  => AllocationStrategy::Fefo->value,
                'valuation_method'     => ValuationMethod::WeightedAverage->value,
            ],
            self::SerializedEquipment => [
                'batch_required'       => false,
                'expiry_required'      => false,
                'serial_required'      => true,
                'location_required'    => true,
                'allow_negative_stock' => false,
                'approval_required'    => false,
                'allocation_strategy'  => AllocationStrategy::Manual->value,
                'valuation_method'     => ValuationMethod::Fifo->value,
            ],
            self::PerishablePetFood => [
                'batch_required'       => true,
                'expiry_required'      => true,
                'serial_required'      => false,
                'location_required'    => false,
                'allow_negative_stock' => false,
                'approval_required'    => false,
                'allocation_strategy'  => AllocationStrategy::Fefo->value,
                'valuation_method'     => ValuationMethod::WeightedAverage->value,
            ],
        };
    }
}
