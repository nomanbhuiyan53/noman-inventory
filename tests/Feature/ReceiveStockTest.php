<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Noman\Inventory\Application\DTOs\ReceiveStockDTO;
use Noman\Inventory\Contracts\InventoryManagerContract;
use Noman\Inventory\Domain\Shared\Enums\DocumentStatus;
use Noman\Inventory\Domain\Shared\ValueObjects\Money;
use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryItem;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockDocument;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockMovement;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryWarehouse;

uses(\Noman\Inventory\Tests\TestCase::class);
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->warehouse = InventoryWarehouse::create([
        'id'        => (string) Str::ulid(),
        'name'      => 'Main Warehouse',
        'code'      => 'WH-01',
        'is_active' => true,
    ]);

    $this->item = InventoryItem::create([
        'id'          => (string) Str::ulid(),
        'name'        => 'Test Item',
        'code'        => 'ITEM-001',
        'is_active'   => true,
        'is_stockable'=> true,
    ]);
});

it('receives stock and creates a posted document', function () {
    $manager = app(InventoryManagerContract::class);

    $result = $manager->receive(new ReceiveStockDTO(
        itemId:      $this->item->id,
        quantity:    Quantity::of(100),
        warehouseId: $this->warehouse->id,
        unitCost:    Money::of(5.00, 'USD'),
        notes:       'Initial stock',
    ));

    expect($result->status)->toBe(DocumentStatus::Posted);
    expect($result->lineCount)->toBe(1);
    expect($result->movementIds)->not->toBeEmpty();

    // Document should exist in DB
    $doc = InventoryStockDocument::find($result->documentId);
    expect($doc)->not->toBeNull();
    expect($doc->status)->toBe(DocumentStatus::Posted);

    // Movement should be in the ledger
    $movement = InventoryStockMovement::find($result->movementIds[0]);
    expect($movement)->not->toBeNull();
    expect($movement->quantity)->toBe(100.0);
    expect($movement->item_id)->toBe($this->item->id);
});

it('receives stock with batch code and creates batch record', function () {
    $manager = app(InventoryManagerContract::class);

    $result = $manager->receive(new ReceiveStockDTO(
        itemId:      $this->item->id,
        quantity:    Quantity::of(50),
        warehouseId: $this->warehouse->id,
        batchCode:   'BATCH-2024-001',
        expiryDate:  '2026-12-31',
        unitCost:    Money::of(10.00, 'USD'),
    ));

    expect($result->isPosted())->toBeTrue();

    $doc = InventoryStockDocument::with('lines')->find($result->documentId);
    $line = $doc->lines->first();

    expect($line->batch_id)->not->toBeNull();

    $batch = \Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryBatch::find($line->batch_id);
    expect($batch->batch_code)->toBe('BATCH-2024-001');
    expect($batch->expiry_date->format('Y-m-d'))->toBe('2026-12-31');
});

it('is idempotent with idempotency key', function () {
    $manager = app(InventoryManagerContract::class);

    $dto = new ReceiveStockDTO(
        itemId:         $this->item->id,
        quantity:       Quantity::of(10),
        warehouseId:    $this->warehouse->id,
        idempotencyKey: 'unique-op-key-123',
    );

    $first  = $manager->receive($dto);
    $second = $manager->receive($dto);

    expect($first->documentId)->toBe($second->documentId);
    expect(InventoryStockDocument::count())->toBe(1);
});
