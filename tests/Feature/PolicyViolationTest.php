<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Noman\Inventory\Application\DTOs\ReceiveStockDTO;
use Noman\Inventory\Contracts\InventoryManagerContract;
use Noman\Inventory\Contracts\PolicyResolverContract;
use Noman\Inventory\Domain\Shared\Enums\AllocationStrategy;
use Noman\Inventory\Domain\Shared\Enums\IndustryProfile;
use Noman\Inventory\Domain\Shared\Enums\ValuationMethod;
use Noman\Inventory\Domain\Shared\Exceptions\PolicyViolationException;
use Noman\Inventory\Domain\Shared\ValueObjects\InventoryPolicy;
use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryItem;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryWarehouse;

uses(\Noman\Inventory\Tests\TestCase::class);
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->warehouse = InventoryWarehouse::create([
        'id'        => (string) Str::ulid(),
        'name'      => 'Test WH',
        'code'      => 'WH-TEST',
        'is_active' => true,
    ]);

    $this->item = InventoryItem::create([
        'id'          => (string) Str::ulid(),
        'name'        => 'Pharma Item',
        'code'        => 'PHM-001',
        'is_active'   => true,
        'is_stockable'=> true,
    ]);
});

it('throws PolicyViolationException when batch is required but not provided', function () {
    // Bind a policy resolver that requires batches
    $this->app->bind(PolicyResolverContract::class, function () {
        return new class implements PolicyResolverContract {
            public function resolveGlobal(): InventoryPolicy
            {
                return new InventoryPolicy(batchRequired: true);
            }

            public function resolveForItemType(string $itemTypeId): InventoryPolicy
            {
                return $this->resolveGlobal();
            }

            public function resolveForItem(string $itemId): InventoryPolicy
            {
                return $this->resolveGlobal();
            }
        };
    });

    $manager = app(InventoryManagerContract::class);

    $manager->receive(new ReceiveStockDTO(
        itemId:      $this->item->id,
        quantity:    Quantity::of(10),
        warehouseId: $this->warehouse->id,
        batchCode:   null, // Missing — should throw
    ));
})->throws(PolicyViolationException::class);

it('throws PolicyViolationException when expiry is required but not provided', function () {
    $this->app->bind(PolicyResolverContract::class, function () {
        return new class implements PolicyResolverContract {
            public function resolveGlobal(): InventoryPolicy
            {
                return new InventoryPolicy(batchRequired: true, expiryRequired: true);
            }

            public function resolveForItemType(string $itemTypeId): InventoryPolicy
            {
                return $this->resolveGlobal();
            }

            public function resolveForItem(string $itemId): InventoryPolicy
            {
                return $this->resolveGlobal();
            }
        };
    });

    $manager = app(InventoryManagerContract::class);

    $manager->receive(new ReceiveStockDTO(
        itemId:      $this->item->id,
        quantity:    Quantity::of(10),
        warehouseId: $this->warehouse->id,
        batchCode:   'BATCH-001',
        expiryDate:  null, // Missing — should throw
    ));
})->throws(PolicyViolationException::class);
