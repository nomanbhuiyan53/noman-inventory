<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Support;

use Illuminate\Support\Facades\Cache;
use Noman\Inventory\Contracts\PolicyResolverContract;
use Noman\Inventory\Domain\Shared\Enums\AllocationStrategy;
use Noman\Inventory\Domain\Shared\Enums\IndustryProfile;
use Noman\Inventory\Domain\Shared\Enums\ValuationMethod;
use Noman\Inventory\Domain\Shared\ValueObjects\InventoryPolicy;

/**
 * Default implementation of PolicyResolverContract.
 *
 * Resolves the effective InventoryPolicy using a layered approach:
 *
 *  Layer 1 (resolveGlobal):
 *    Reads from the published inventory.php config.
 *
 *  Layer 2 (resolveForItemType):
 *    Merges item-type-level overrides from the database on top of global policy.
 *    Falls back gracefully if no DB record exists.
 *
 *  Layer 3 (resolveForItem):
 *    Merges item-level overrides on top of the item-type policy.
 *    Falls back gracefully if no DB record exists.
 *
 * Results are cached for the duration of the request to avoid repeated DB hits.
 * The cache key includes the item/type ID so changes are isolated.
 *
 * Host applications that need tenant-level policy overrides should provide
 * their own PolicyResolverContract implementation and bind it in their
 * service provider.
 */
final class DefaultPolicyResolver implements PolicyResolverContract
{
    public function resolveGlobal(): InventoryPolicy
    {
        $config = config('inventory');

        return InventoryPolicy::fromArray([
            'allow_negative_stock'       => $config['allow_negative_stock'] ?? false,
            'batch_required'             => false,
            'expiry_required'            => false,
            'serial_required'            => false,
            'location_required'          => $config['multi_warehouse'] ?? false,
            'approval_required'          => false,
            'allocation_strategy'        => $config['allocation_strategy'] ?? AllocationStrategy::Fefo->value,
            'valuation_method'           => $config['valuation_method'] ?? ValuationMethod::WeightedAverage->value,
            'reservation_expiry_minutes' => $config['reservation_expiry_minutes'] ?? null,
            'industry_profile'           => $config['default_industry_profile'] ?? IndustryProfile::StandardGoods->value,
        ]);
    }

    public function resolveForItemType(string $itemTypeId): InventoryPolicy
    {
        $cacheKey = "inventory.policy.type.{$itemTypeId}";

        return Cache::remember($cacheKey, 60, function () use ($itemTypeId): InventoryPolicy {
            $basePolicy = $this->resolveGlobal();

            // TODO: Load item-type policy overrides from the database here.
            // Example:
            //   $type = InventoryItemType::find($itemTypeId);
            //   if ($type && $type->policy_overrides) {
            //       return $basePolicy->merge($type->policy_overrides);
            //   }

            return $basePolicy;
        });
    }

    public function resolveForItem(string $itemId): InventoryPolicy
    {
        $cacheKey = "inventory.policy.item.{$itemId}";

        return Cache::remember($cacheKey, 60, function () use ($itemId): InventoryPolicy {
            // TODO: Load item-level policy overrides from the database here.
            // Example:
            //   $item = InventoryItem::find($itemId);
            //   if (! $item) {
            //       return $this->resolveGlobal();
            //   }
            //   $typePolicy = $this->resolveForItemType($item->item_type_id);
            //   if ($item->policy_overrides) {
            //       return $typePolicy->merge($item->policy_overrides);
            //   }
            //   return $typePolicy;

            return $this->resolveGlobal();
        });
    }
}
