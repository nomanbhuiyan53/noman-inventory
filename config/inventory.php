<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant Mode
    |--------------------------------------------------------------------------
    | Controls how tenancy is handled. Options:
    |   null       - Single-tenant mode, no tenant_id filtering
    |   'column'   - Multi-tenant via tenant_id column on all tables
    | The host application must bind a TenantResolverContract implementation
    | to supply the current tenant context.
    */
    'tenant_mode' => env('INVENTORY_TENANT_MODE', null),

    /*
    |--------------------------------------------------------------------------
    | Negative Stock
    |--------------------------------------------------------------------------
    | Global default for whether stock can go negative.
    | Can be overridden at item-type or item level via policies.
    */
    'allow_negative_stock' => env('INVENTORY_ALLOW_NEGATIVE_STOCK', false),

    /*
    |--------------------------------------------------------------------------
    | Allocation Strategy
    |--------------------------------------------------------------------------
    | Default stock allocation strategy for issue/transfer operations.
    | Options: fifo, fefo, manual
    | FEFO (First Expired First Out) is recommended for perishable goods.
    | FIFO (First In First Out) is recommended for non-perishable goods.
    */
    'allocation_strategy' => env('INVENTORY_ALLOCATION_STRATEGY', 'fefo'),

    /*
    |--------------------------------------------------------------------------
    | Valuation Method
    |--------------------------------------------------------------------------
    | Default costing/valuation method for inventory.
    | Options: fifo, weighted_average, standard_cost
    */
    'valuation_method' => env('INVENTORY_VALUATION_METHOD', 'weighted_average'),

    /*
    |--------------------------------------------------------------------------
    | Reservations
    |--------------------------------------------------------------------------
    | Enable or disable the reservation subsystem.
    | When enabled, stock can be soft-reserved before actual issue.
    | reservation_expiry_minutes: null means reservations never expire automatically.
    */
    'reservations_enabled'          => env('INVENTORY_RESERVATIONS_ENABLED', true),
    'reservation_expiry_minutes'    => env('INVENTORY_RESERVATION_EXPIRY_MINUTES', null),

    /*
    |--------------------------------------------------------------------------
    | Batch / Lot Tracking
    |--------------------------------------------------------------------------
    | Enable batch/lot tracking globally.
    | Can be required per item or item-type via policies.
    */
    'batching_enabled' => env('INVENTORY_BATCHING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Expiry Tracking
    |--------------------------------------------------------------------------
    | Number of days before expiry date to trigger expiry alerts.
    */
    'expiry_alert_days' => env('INVENTORY_EXPIRY_ALERT_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Multi-Warehouse Mode
    |--------------------------------------------------------------------------
    | When true, every stock movement must reference a warehouse and location.
    | When false, a default virtual warehouse/location is assumed.
    */
    'multi_warehouse' => env('INVENTORY_MULTI_WAREHOUSE', true),

    /*
    |--------------------------------------------------------------------------
    | Bin / Zone Tracking
    |--------------------------------------------------------------------------
    | Enable fine-grained bin-level stock tracking within locations.
    */
    'bin_tracking' => env('INVENTORY_BIN_TRACKING', false),

    /*
    |--------------------------------------------------------------------------
    | HTTP Routes
    |--------------------------------------------------------------------------
    | Whether to register the package's HTTP routes.
    | Set to false if you want to define your own routes manually.
    */
    'routes_enabled' => env('INVENTORY_ROUTES_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | API Middleware
    |--------------------------------------------------------------------------
    | Middleware applied to all inventory API routes.
    */
    'api_middleware' => ['api'],

    /*
    |--------------------------------------------------------------------------
    | Web (Blade) Middleware
    |--------------------------------------------------------------------------
    | Middleware applied to inventory web UI routes (e.g. session, auth).
    */
    'web_middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    | Prefix for all inventory API routes.
    */
    'route_prefix' => env('INVENTORY_ROUTE_PREFIX', 'inventory'),

    /*
    |--------------------------------------------------------------------------
    | Document Types Requiring Approval
    |--------------------------------------------------------------------------
    | List of document types that must be approved before they can be posted.
    | Example: ['adjustment', 'transfer']
    */
    'approval_required_for' => [],

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    | Default currency code (ISO 4217) for monetary values.
    */
    'currency' => env('INVENTORY_CURRENCY', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | Default Industry Profile
    |--------------------------------------------------------------------------
    | The default industry profile to apply when no profile is explicitly set.
    | Options: standard_goods, pharma_goods, livestock_supply,
    |          serialized_equipment, perishable_pet_food
    */
    'default_industry_profile' => env('INVENTORY_DEFAULT_PROFILE', 'standard_goods'),

    /*
    |--------------------------------------------------------------------------
    | Projection / Read Model Updates
    |--------------------------------------------------------------------------
    | When queue_projections is true, balance and summary projection updates
    | are dispatched as queued jobs instead of being updated synchronously.
    | Useful for high-throughput environments.
    */
    'queue_projections'  => env('INVENTORY_QUEUE_PROJECTIONS', false),
    'projection_queue'   => env('INVENTORY_PROJECTION_QUEUE', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Table Name Overrides
    |--------------------------------------------------------------------------
    | Override any table name to fit your database naming conventions.
    */
    'tables' => [
        'items'                       => 'inventory_items',
        'item_types'                  => 'inventory_item_types',
        'categories'                  => 'inventory_categories',
        'item_variants'               => 'inventory_item_variants',
        'units'                       => 'inventory_units',
        'unit_conversions'            => 'inventory_unit_conversions',
        'warehouses'                  => 'inventory_warehouses',
        'locations'                   => 'inventory_locations',
        'batches'                     => 'inventory_batches',
        'serial_numbers'              => 'inventory_serial_numbers',
        'stock_documents'             => 'inventory_stock_documents',
        'stock_document_lines'        => 'inventory_stock_document_lines',
        'stock_movements'             => 'inventory_stock_movements',
        'reservations'                => 'inventory_reservations',
        'allocations'                 => 'inventory_allocations',
        'valuation_entries'           => 'inventory_valuation_entries',
        'stock_count_sessions'        => 'inventory_stock_count_sessions',
        'stock_count_entries'         => 'inventory_stock_count_entries',
        'stock_adjustments'           => 'inventory_stock_adjustments',
        'stock_balances'              => 'inventory_stock_balances',
        'stock_balance_snapshots'     => 'inventory_stock_balance_snapshots',
        'inventory_aging'             => 'inventory_inventory_aging',
        'batch_expiry_summary'        => 'inventory_batch_expiry_summary',
        'custom_fields'               => 'inventory_custom_fields',
        'custom_field_values'         => 'inventory_custom_field_values',
        'tags'                        => 'inventory_tags',
        'item_tag_maps'               => 'inventory_item_tag_maps',
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Binding Overrides
    |--------------------------------------------------------------------------
    | Override any core service implementation by binding your own class here.
    | Each key corresponds to a contract interface; the value is the class to bind.
    */
    'bindings' => [
        'tenant_resolver'           => \Noman\Inventory\Infrastructure\Support\NullTenantResolver::class,
        'policy_resolver'           => \Noman\Inventory\Infrastructure\Support\DefaultPolicyResolver::class,
        'document_number_generator' => \Noman\Inventory\Infrastructure\Support\DefaultDocumentNumberGenerator::class,
    ],

];
