<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Noman\Inventory\Application\DTOs\ReceiveStockDTO;
use Noman\Inventory\Application\DTOs\ReverseDocumentDTO;
use Noman\Inventory\Contracts\InventoryManagerContract;
use Noman\Inventory\Domain\Shared\Enums\DocumentStatus;
use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryItem;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockDocument;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockMovement;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryWarehouse;

uses(\Noman\Inventory\Tests\TestCase::class);
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('reverses a posted document with compensating movements', function () {
    $warehouse = InventoryWarehouse::create(['id' => (string) Str::ulid(), 'name' => 'WH', 'code' => 'WH-1', 'is_active' => true]);
    $item      = InventoryItem::create(['id' => (string) Str::ulid(), 'name' => 'Item', 'code' => 'IT-1', 'is_active' => true, 'is_stockable' => true]);

    $manager = app(InventoryManagerContract::class);

    // Receive 100 units
    $receiveResult = $manager->receive(new ReceiveStockDTO(
        itemId:      $item->id,
        quantity:    Quantity::of(100),
        warehouseId: $warehouse->id,
    ));

    expect($receiveResult->isPosted())->toBeTrue();
    expect(InventoryStockMovement::count())->toBe(1);

    // Reverse the document
    $reverseResult = $manager->reverseDocument(new ReverseDocumentDTO(
        documentId: $receiveResult->documentId,
        reason:     'Wrong item received',
    ));

    expect($reverseResult->isPosted())->toBeTrue();
    expect($reverseResult->reversalOf)->toBe($receiveResult->documentId);

    // Original document should be marked reversed
    $original = InventoryStockDocument::find($receiveResult->documentId);
    expect($original->status)->toBe(DocumentStatus::Reversed);

    // A new reversal document should exist
    $reversal = InventoryStockDocument::find($reverseResult->documentId);
    expect($reversal->document_type)->toBe('reversal');
    expect($reversal->status)->toBe(DocumentStatus::Posted);

    // Two movement rows should exist: original + compensating
    expect(InventoryStockMovement::count())->toBe(2);

    $movements = InventoryStockMovement::orderBy('created_at')->get();
    expect($movements[0]->quantity)->toBe(100.0);   // original: +100
    expect($movements[1]->quantity)->toBe(-100.0);   // reversal: -100
});

it('cannot reverse an already-reversed document', function () {
    $warehouse = InventoryWarehouse::create(['id' => (string) Str::ulid(), 'name' => 'WH', 'code' => 'WH-2', 'is_active' => true]);
    $item      = InventoryItem::create(['id' => (string) Str::ulid(), 'name' => 'Item', 'code' => 'IT-2', 'is_active' => true, 'is_stockable' => true]);

    $manager = app(InventoryManagerContract::class);

    $receiveResult = $manager->receive(new ReceiveStockDTO(
        itemId:      $item->id,
        quantity:    Quantity::of(10),
        warehouseId: $warehouse->id,
    ));

    $manager->reverseDocument(new ReverseDocumentDTO(
        documentId: $receiveResult->documentId,
        reason:     'First reversal',
    ));

    // Attempting to reverse again should throw
    $manager->reverseDocument(new ReverseDocumentDTO(
        documentId: $receiveResult->documentId,
        reason:     'Second reversal attempt',
    ));
})->throws(\Noman\Inventory\Domain\Shared\Exceptions\DocumentException::class);
