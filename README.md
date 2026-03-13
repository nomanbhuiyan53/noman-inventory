# noman-inventory

**Package:** [`nomandev/noman-inventory`](https://packagist.org/packages/nomandev/noman-inventory) · **Namespace:** `Noman\Inventory`

A **production-grade, universal inventory management package** for Laravel 11 and 12.

Built for multi-tenant, multi-warehouse environments across diverse business domains: cow farms, pharmaceutical distributors, pet shops, clinics, warehouses, general retail, and more.

---

## Features


| Feature                | Details                                                                      |
| ---------------------- | ---------------------------------------------------------------------------- |
| **Append-only ledger** | All stock changes recorded as immutable movement rows. Never destructive.    |
| **Documents**          | GRNs, Delivery Orders, Transfer Orders, Adjustments, Stock Counts, Reversals |
| **Batches / Lots**     | Full batch tracking with expiry date and FEFO/FIFO allocation                |
| **Serial Numbers**     | Unit-level serial tracking for equipment and high-value items                |
| **Valuation**          | FIFO, Weighted Average, Standard Cost                                        |
| **Reservations**       | Soft-lock stock before issue; automatic expiry; reference linking            |
| **Stock Counts**       | Full count session workflow with variance calculation and auto-adjustment    |
| **Reversals**          | Compensating entries; original documents never modified                      |
| **Projections**        | Denormalised balance + snapshot tables for fast reporting                    |
| **Multi-warehouse**    | Hierarchical: Warehouse → Zone → Aisle → Rack → Shelf → Bin                  |
| **Multi-tenant**       | Pluggable `TenantResolverContract`; no tenancy package hard-coded            |
| **Industry Profiles**  | Standard Goods, Pharma, Livestock Supply, Serialised Equipment, Pet Food     |
| **Policy Engine**      | Global → item-type → item level policy overrides                             |
| **REST API**           | Full CRUD + all stock operations + reports                                   |
| **Blade UI**           | Optional web UI: dashboard, items, warehouses, stock ops, documents, reports |
| **Events**             | 12 domain events; listeners for balance projection                           |


---

## Requirements

- PHP **8.3+**
- Laravel **11.x** or **12.x**
- `spatie/laravel-package-tools` ^1.16

---

## Installation

```bash
composer require nomandev/noman-inventory
```

The package auto-discovers via Laravel's package discovery mechanism.

---

## Publishing Config & Migrations

```bash
# Publish the configuration file
php artisan vendor:publish --tag="noman-inventory-config"

# Publish migrations (then review before running)
php artisan vendor:publish --tag="noman-inventory-migrations"

# Run migrations
php artisan migrate
```

---

## Configuration Overview

```php
// config/inventory.php

return [
    'tenant_mode'            => null,           // null = single-tenant
    'allow_negative_stock'   => false,
    'allocation_strategy'    => 'fefo',         // fefo | fifo | manual
    'valuation_method'       => 'weighted_average', // weighted_average | fifo | standard_cost
    'reservations_enabled'   => true,
    'reservation_expiry_minutes' => null,
    'batching_enabled'       => true,
    'expiry_alert_days'      => 30,
    'multi_warehouse'        => true,
    'bin_tracking'           => false,
    'routes_enabled'         => true,
    'api_middleware'         => ['api'],
    'route_prefix'           => 'inventory',
    'approval_required_for'  => [],            // ['adjustment', 'transfer']
    'currency'               => 'USD',
    'default_industry_profile' => 'standard_goods',
    'queue_projections'      => false,
    'tables'                 => [...],          // Override table names
    'bindings'               => [...],          // Override service implementations
];
```

---

## Basic Setup

### 1. Create warehouses and items

```php
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryWarehouse;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryItem;

$warehouse = InventoryWarehouse::create([
    'id'        => (string) \Illuminate\Support\Str::ulid(),
    'name'      => 'Main Warehouse',
    'code'      => 'WH-MAIN',
    'is_active' => true,
]);

$item = InventoryItem::create([
    'id'          => (string) \Illuminate\Support\Str::ulid(),
    'name'        => 'Paracetamol 500mg',
    'code'        => 'PARA-500',
    'sku'         => 'SKU-PARA-500',
    'is_active'   => true,
    'is_stockable'=> true,
    'industry_profile' => 'pharma_goods',
]);
```

### 2. Receive stock

```php
use Noman\Inventory\Support\Facades\NomanInventoryFacade as NomanInventory;
use Noman\Inventory\Application\DTOs\ReceiveStockDTO;
use Noman\Inventory\Domain\Shared\ValueObjects\{Quantity, Money};

$result = NomanInventory::receive(new ReceiveStockDTO(
    itemId:      $item->id,
    quantity:    Quantity::of(500),
    warehouseId: $warehouse->id,
    unitCost:    Money::of(0.25, 'USD'),
    batchCode:   'BATCH-2024-001',
    expiryDate:  '2026-06-30',
    referenceDocNumber: 'PO-2024-00123',
));

echo $result->documentNumber;  // GRN-20241201-A3F2B1
echo $result->status->label(); // Posted
```

### 3. Issue stock

```php
use Noman\Inventory\Application\DTOs\IssueStockDTO;
use Noman\Inventory\Domain\Shared\Enums\MovementType;

$result = NomanInventory::issue(new IssueStockDTO(
    itemId:        $item->id,
    quantity:      Quantity::of(50),
    warehouseId:   $warehouse->id,
    movementType:  MovementType::SaleOut,
    referenceDocNumber: 'SO-2024-00456',
));
```

### 4. Transfer stock between warehouses

```php
use Noman\Inventory\Application\DTOs\TransferStockDTO;

$result = NomanInventory::transfer(new TransferStockDTO(
    itemId:          $item->id,
    quantity:        Quantity::of(100),
    fromWarehouseId: $warehouseA->id,
    toWarehouseId:   $warehouseB->id,
));
```

### 5. Reserve stock

```php
use Noman\Inventory\Application\DTOs\ReserveStockDTO;

$reservationId = NomanInventory::reserve(new ReserveStockDTO(
    itemId:        $item->id,
    quantity:      Quantity::of(20),
    warehouseId:   $warehouse->id,
    referenceType: 'sales_order',
    referenceId:   'SO-2024-00789',
    expiryMinutes: 60,
));

// Later, release it:
NomanInventory::releaseReservation($reservationId);
```

### 6. Adjust stock

```php
use Noman\Inventory\Application\DTOs\AdjustStockDTO;

// Positive = stock found; negative = stock lost
$result = NomanInventory::adjust(new AdjustStockDTO(
    itemId:      $item->id,
    quantity:    Quantity::of(-5),   // 5 units short
    warehouseId: $warehouse->id,
    reason:      'Damage found during count',
));
```

### 7. Reverse a document

```php
use Noman\Inventory\Application\DTOs\ReverseDocumentDTO;

$reversal = NomanInventory::reverseDocument(new ReverseDocumentDTO(
    documentId: $result->documentId,
    reason:     'Incorrect item received — returning to vendor',
));
```

### 8. Check balance

```php
$available = NomanInventory::getBalance($item->id, $warehouse->id);
echo $available; // 445
```

---

## Stock Count Workflow

```php
use Noman\Inventory\Application\Actions\StartStockCountAction;
use Noman\Inventory\Application\Actions\CompleteStockCountAction;

// 1. Start the count
$session = app(StartStockCountAction::class)->execute(
    warehouseId: $warehouse->id,
    countDate:   '2024-12-01',
);

// 2. Complete with actual counted quantities
app(CompleteStockCountAction::class)->execute(
    sessionId:  $session->id,
    counts: [
        ['entry_id' => $entryId, 'counted_quantity' => 98],
    ],
    autoAdjust: true,   // posts adjustment docs for all variances
);
```

---

## Tenancy Integration

The package is fully multi-tenant ready but ships with a no-op `NullTenantResolver`. To enable tenancy, bind your own implementation:

```php
// In your AppServiceProvider:
use Noman\Inventory\Contracts\TenantResolverContract;
use Noman\Inventory\Domain\Shared\ValueObjects\TenantId;

$this->app->bind(TenantResolverContract::class, function () {
    return new class implements TenantResolverContract {
        public function getCurrentTenantId(): ?TenantId
        {
            $tenantId = app('current_tenant')?->id; // your tenancy hook
            return $tenantId ? TenantId::of($tenantId) : null;
        }

        public function hasTenant(): bool
        {
            return app('current_tenant') !== null;
        }
    };
});
```

Also set in `.env`:

```dotenv
INVENTORY_TENANT_MODE=column
```

---

## Industry Profiles

Assign a profile to item types or items to automatically apply the right policy defaults:


| Profile                | Batch | Expiry | Serial | Location | Allocation |
| ---------------------- | ----- | ------ | ------ | -------- | ---------- |
| `standard_goods`       | ❌     | ❌      | ❌      | ❌        | FIFO       |
| `pharma_goods`         | ✅     | ✅      | ❌      | ✅        | FEFO       |
| `livestock_supply`     | ✅     | ✅      | ❌      | ❌        | FEFO       |
| `serialized_equipment` | ❌     | ❌      | ✅      | ✅        | Manual     |
| `perishable_pet_food`  | ✅     | ✅      | ❌      | ❌        | FEFO       |


---

## Extension Points

### Override any service implementation

```php
// config/inventory.php
'bindings' => [
    'tenant_resolver'           => \App\Services\CurrentTenantResolver::class,
    'policy_resolver'           => \App\Services\DatabasePolicyResolver::class,
    'document_number_generator' => \App\Services\SequentialDocumentNumberGenerator::class,
    'stock_allocator'           => \App\Services\ZoneRestrictedAllocator::class,
    'stock_valuator'            => \App\Services\FifoValuator::class,
],
```

### Listen to inventory events

```php
// In EventServiceProvider:
protected $listen = [
    \Noman\Inventory\Domain\Inventory\Events\StockReceived::class => [
        \App\Listeners\NotifyPurchasingOnReceipt::class,
    ],
    \Noman\Inventory\Domain\Inventory\Events\BatchExpired::class => [
        \App\Listeners\AlertQualityControlTeam::class,
    ],
    \Noman\Inventory\Domain\Inventory\Events\StockReserved::class => [
        \App\Listeners\UpdateSalesOrderStatus::class,
    ],
];
```

### Custom item metadata

Use the `metadata` JSON column on `inventory_items` for host-app-specific fields:

```php
$item->update([
    'metadata' => [
        'cow_breed'    => 'Holstein',
        'farm_id'      => 42,
        'vaccination_status' => 'up_to_date',
    ],
]);
```

Or use the `inventory_custom_fields` + `inventory_custom_field_values` tables for structured custom field definitions.

---

## API Endpoints

All endpoints are prefixed with `/inventory` (configurable via `route_prefix`).


| Method | Endpoint                                | Description                  |
| ------ | --------------------------------------- | ---------------------------- |
| GET    | `/inventory/items`                      | List items                   |
| POST   | `/inventory/items`                      | Create item                  |
| GET    | `/inventory/items/{id}`                 | Get item                     |
| PUT    | `/inventory/items/{id}`                 | Update item                  |
| DELETE | `/inventory/items/{id}`                 | Delete item                  |
| POST   | `/inventory/stock/receive`              | Receive stock                |
| POST   | `/inventory/stock/issue`                | Issue stock                  |
| POST   | `/inventory/stock/transfer`             | Transfer stock               |
| POST   | `/inventory/stock/adjust`               | Adjust stock                 |
| POST   | `/inventory/stock/reserve`              | Reserve stock                |
| DELETE | `/inventory/stock/reserve/{id}`         | Release reservation          |
| GET    | `/inventory/documents`                  | List documents               |
| GET    | `/inventory/documents/{id}`             | Get document                 |
| POST   | `/inventory/documents/{id}/post`        | Post document                |
| POST   | `/inventory/documents/{id}/reverse`     | Reverse document             |
| POST   | `/inventory/stock-counts/start`         | Start stock count            |
| POST   | `/inventory/stock-counts/{id}/complete` | Complete count               |
| GET    | `/inventory/reports/stock-on-hand`      | Stock on hand report         |
| GET    | `/inventory/reports/stock-by-location`  | Stock by location            |
| GET    | `/inventory/reports/stock-ledger`       | Stock movement ledger        |
| GET    | `/inventory/reports/stock-card`         | Stock card (running balance) |
| GET    | `/inventory/reports/batch-expiry`       | Batch expiry report          |
| GET    | `/inventory/reports/inventory-aging`    | Inventory aging              |
| GET    | `/inventory/reports/valuation-summary`  | Valuation summary            |
| GET    | `/inventory/reports/reservations`       | Reservation status           |


---

## Web UI (Blade)

The package includes an optional Blade-based web UI, using the same prefix as the API (default `/inventory`). Enable or disable it via `config('inventory.routes_enabled')`; middleware is set with `config('inventory.web_middleware', ['web'])`.


| URL                             | Description                                                                    |
| ------------------------------- | ------------------------------------------------------------------------------ |
| `GET /inventory`                | Dashboard                                                                      |
| `GET /inventory/items`          | List items (create, edit, show, delete)                                        |
| `GET /inventory/warehouses`     | List warehouses (create, edit, show, delete)                                   |
| `GET /inventory/stock/receive`  | Receive stock form                                                             |
| `GET /inventory/stock/issue`    | Issue stock form                                                               |
| `GET /inventory/stock/transfer` | Transfer stock form                                                            |
| `GET /inventory/stock/adjust`   | Adjust stock form                                                              |
| `GET /inventory/documents`      | Stock documents list and detail                                                |
| `GET /inventory/stock-counts`   | Stock count sessions; start new count                                          |
| `GET /inventory/reports`        | Reports index (stock on hand, by location, ledger, batch expiry, reservations) |


Views live in the package under `resources/views` and are registered with the `noman-inventory` view namespace. To customise the UI, publish the views:

```bash
php artisan vendor:publish --tag="noman-inventory-views"
```

Then edit the published Blade files in `resources/views/vendor/noman-inventory/`.

---

## Running Tests

```bash
cd packages/noman-inventory

# Install dev dependencies
composer install

# Run the full test suite
./vendor/bin/pest

# Run with coverage
./vendor/bin/pest --coverage
```

---

## Architecture

```
src/
├── Contracts/               # 7 interface contracts (public API seams)
├── Domain/
│   ├── Shared/
│   │   ├── Enums/           # MovementType, DocumentStatus, AllocationStrategy, ...
│   │   ├── ValueObjects/    # Quantity, Money, Sku, BatchCode, InventoryPolicy, ...
│   │   └── Exceptions/      # Domain exception hierarchy
│   └── Inventory/
│       └── Events/          # 12 domain events
├── Application/
│   ├── Actions/             # Use cases: Receive, Issue, Transfer, Adjust, Reserve, ...
│   ├── DTOs/                # Input/output data objects
│   └── Queries/             # Query parameter objects for reporting
└── Infrastructure/
    ├── Providers/           # NomanInventoryServiceProvider
    ├── Persistence/
    │   ├── Eloquent/        # 22 Eloquent models
    │   └── Repositories/    # 6 repository classes
    ├── Allocation/          # FefoAllocator (FEFO + FIFO)
    ├── Valuation/           # WeightedAverageValuator
    ├── Reporting/           # EloquentInventoryReporter
    ├── Listeners/           # Projection update listeners
    └── Support/             # NullTenantResolver, DefaultPolicyResolver, ...
```

---

## License

MIT