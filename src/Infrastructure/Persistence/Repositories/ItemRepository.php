<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Repositories;

use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryItem;

/**
 * Repository for reading and writing InventoryItem records.
 * All methods apply tenant scoping via the tenantId parameter.
 */
class ItemRepository
{
    public function findById(string $itemId, ?string $tenantId = null): ?InventoryItem
    {
        return InventoryItem::query()
            ->forTenant($tenantId)
            ->find($itemId);
    }

    public function findByCode(string $code, ?string $tenantId = null): ?InventoryItem
    {
        return InventoryItem::query()
            ->forTenant($tenantId)
            ->where('code', $code)
            ->first();
    }

    public function findBySku(string $sku, ?string $tenantId = null): ?InventoryItem
    {
        return InventoryItem::query()
            ->forTenant($tenantId)
            ->where('sku', $sku)
            ->first();
    }

    public function findByBarcode(string $barcode, ?string $tenantId = null): ?InventoryItem
    {
        return InventoryItem::query()
            ->forTenant($tenantId)
            ->where('barcode', $barcode)
            ->first();
    }

    /**
     * @return InventoryItem[]
     */
    public function findAll(?string $tenantId = null, bool $activeOnly = true): array
    {
        return InventoryItem::query()
            ->forTenant($tenantId)
            ->when($activeOnly, fn ($q) => $q->active())
            ->get()
            ->all();
    }

    public function save(InventoryItem $item): InventoryItem
    {
        $item->save();

        return $item;
    }

    public function delete(InventoryItem $item): void
    {
        $item->delete();
    }
}
