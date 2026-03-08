<?php

declare(strict_types=1);

namespace Noman\Inventory\Contracts;

use Noman\Inventory\Domain\Shared\ValueObjects\InventoryPolicy;

/**
 * Contract for resolving the effective InventoryPolicy for a given item.
 *
 * Policy is resolved in layers (from least to most specific):
 *   1. Global config defaults
 *   2. Industry profile defaults
 *   3. Item-type level overrides
 *   4. Item-level overrides
 *
 * The DefaultPolicyResolver reads global config and DB-stored policy metadata.
 * Host applications may provide their own implementation to add tenant-level
 * policy overrides or to pull policies from external configuration systems.
 */
interface PolicyResolverContract
{
    /**
     * Resolve the effective inventory policy purely from global config defaults.
     * Used as the starting baseline before item-specific overrides are applied.
     */
    public function resolveGlobal(): InventoryPolicy;

    /**
     * Resolve the effective policy for a given item type (applies item-type overrides
     * on top of the global policy).
     *
     * @param string $itemTypeId The UUID/ULID of the item type
     */
    public function resolveForItemType(string $itemTypeId): InventoryPolicy;

    /**
     * Resolve the fully-merged policy for a specific item.
     * This is the method called by Actions before posting stock operations.
     *
     * @param string $itemId The UUID/ULID of the inventory item
     */
    public function resolveForItem(string $itemId): InventoryPolicy;
}
