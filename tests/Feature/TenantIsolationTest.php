<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Noman\Inventory\Application\DTOs\ReceiveStockDTO;
use Noman\Inventory\Contracts\InventoryManagerContract;
use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryItem;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockDocument;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryWarehouse;

uses(\Noman\Inventory\Tests\TestCase::class);
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('isolates documents by tenant_id', function () {
    $wh1  = InventoryWarehouse::create(['id' => (string) Str::ulid(), 'name' => 'WH-T1', 'code' => 'WH-T1', 'is_active' => true]);
    $wh2  = InventoryWarehouse::create(['id' => (string) Str::ulid(), 'name' => 'WH-T2', 'code' => 'WH-T2', 'is_active' => true]);
    $item = InventoryItem::create(['id' => (string) Str::ulid(), 'name' => 'Item', 'code' => 'IT-X', 'is_active' => true, 'is_stockable' => true]);

    $manager = app(InventoryManagerContract::class);

    // Tenant A receives stock
    $manager->receive(new ReceiveStockDTO(
        itemId:      $item->id,
        quantity:    Quantity::of(50),
        warehouseId: $wh1->id,
        tenantId:    'tenant-A',
    ));

    // Tenant B receives stock
    $manager->receive(new ReceiveStockDTO(
        itemId:      $item->id,
        quantity:    Quantity::of(100),
        warehouseId: $wh2->id,
        tenantId:    'tenant-B',
    ));

    $tenantADocs = InventoryStockDocument::where('tenant_id', 'tenant-A')->count();
    $tenantBDocs = InventoryStockDocument::where('tenant_id', 'tenant-B')->count();

    expect($tenantADocs)->toBe(1);
    expect($tenantBDocs)->toBe(1);
    expect(InventoryStockDocument::count())->toBe(2);
});
