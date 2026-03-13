<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Providers;

use Noman\Inventory\Contracts\DocumentNumberGeneratorContract;
use Noman\Inventory\Contracts\InventoryManagerContract;
use Noman\Inventory\Contracts\InventoryReporterContract;
use Noman\Inventory\Contracts\PolicyResolverContract;
use Noman\Inventory\Contracts\StockAllocatorContract;
use Noman\Inventory\Contracts\StockValuatorContract;
use Noman\Inventory\Contracts\TenantResolverContract;
use Noman\Inventory\Infrastructure\Allocation\FefoAllocator;
use Noman\Inventory\Infrastructure\Reporting\EloquentInventoryReporter;
use Noman\Inventory\Infrastructure\Support\DefaultDocumentNumberGenerator;
use Noman\Inventory\Infrastructure\Support\DefaultPolicyResolver;
use Noman\Inventory\Infrastructure\Support\NullTenantResolver;
use Noman\Inventory\Infrastructure\Valuation\WeightedAverageValuator;
use Noman\Inventory\Application\Actions\InventoryManager;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * Main service provider for the noman-inventory package.
 *
 * Overrides getPackageBaseDir() so the package root (where composer.json and
 * routes/ live) is used, not the provider's directory (src/Infrastructure/Providers).
 *
 * Bootstrapped via Spatie's PackageServiceProvider which handles:
 *  - Config file publishing (inventory.php)
 *  - Migration loading / publishing
 *  - Route loading
 *  - View / translation loading
 *
 * Contract-to-implementation bindings are registered in packageRegistered()
 * and can be overridden by host applications by re-binding in their own
 * service providers (which run after package providers).
 *
 * Event listeners and observers are registered in packageBooted().
 */
class NomanInventoryServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('noman-inventory')
            ->hasConfigFile('inventory')
            ->hasMigrations([
                '2024_01_01_000001_create_inventory_item_types_table',
                '2024_01_01_000002_create_inventory_categories_table',
                '2024_01_01_000003_create_inventory_units_table',
                '2024_01_01_000004_create_inventory_unit_conversions_table',
                '2024_01_01_000005_create_inventory_items_table',
                '2024_01_01_000006_create_inventory_item_variants_table',
                '2024_01_01_000007_create_inventory_warehouses_table',
                '2024_01_01_000008_create_inventory_locations_table',
                '2024_01_01_000009_create_inventory_batches_table',
                '2024_01_01_000010_create_inventory_serial_numbers_table',
                '2024_01_01_000011_create_inventory_stock_documents_table',
                '2024_01_01_000012_create_inventory_stock_document_lines_table',
                '2024_01_01_000013_create_inventory_stock_movements_table',
                '2024_01_01_000014_create_inventory_reservations_table',
                '2024_01_01_000015_create_inventory_allocations_table',
                '2024_01_01_000016_create_inventory_valuation_entries_table',
                '2024_01_01_000017_create_inventory_stock_count_sessions_table',
                '2024_01_01_000018_create_inventory_stock_count_entries_table',
                '2024_01_01_000019_create_inventory_stock_adjustments_table',
                '2024_01_01_000020_create_inventory_stock_balances_table',
                '2024_01_01_000021_create_inventory_stock_balance_snapshots_table',
                '2024_01_01_000022_create_inventory_inventory_aging_table',
                '2024_01_01_000023_create_inventory_batch_expiry_summary_table',
                '2024_01_01_000024_create_inventory_custom_fields_table',
                '2024_01_01_000025_create_inventory_custom_field_values_table',
                '2024_01_01_000026_create_inventory_tags_table',
                '2024_01_01_000027_create_inventory_item_tag_maps_table',
            ])
            ->hasTranslations()
            ->hasViews();
    }

    /**
     * Spatie expects basePath to be package_root/src so that
     * basePath("/../config/...") and basePath("/../database/migrations/...")
     * resolve to package_root/config and package_root/database/migrations.
     * (Provider is at src/Infrastructure/Providers/, so dirname 3 levels up = package root.)
     */
    protected function getPackageBaseDir(): string
    {
        return dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src';
    }

    /**
     * Override to prevent Spatie from loading routes (which uses a wrong path when
     * the package is in vendor/ without routes/). We load routes ourselves in
     * packageBooted() only when the files exist.
     */
    protected function bootPackageRoutes(): static
    {
        return $this;
    }

    /**
     * Register package bindings.
     * All bindings are soft (bindIf) so host apps can override them.
     */
    public function packageRegistered(): void
    {
        $this->bindContractsFromConfig();
        $this->app->bindIf(InventoryManagerContract::class, InventoryManager::class);
    }

    /**
     * Boot event listeners, observers, and console commands.
     */
    public function packageBooted(): void
    {
        $this->loadRoutesFromPackage();
        $this->registerEventListeners();
        $this->registerConsoleCommands();
    }

    /**
     * Load route files only if they exist, so the app boots even when the
     * installed package has no routes/ directory (e.g. some dist archives).
     * Routes live at package root, not in src/.
     */
    private function loadRoutesFromPackage(): void
    {
        $packageRoot = dirname(__DIR__, 3);
        $apiPath     = $packageRoot . '/routes/api.php';
        $webPath     = $packageRoot . '/routes/web.php';
        if (is_file($apiPath)) {
            $this->loadRoutesFrom($apiPath);
        }
        if (is_file($webPath)) {
            $this->loadRoutesFrom($webPath);
        }
    }

    // -------------------------------------------------------------------------
    // Internal wiring
    // -------------------------------------------------------------------------

    /**
     * Bind contracts to their implementations, respecting host-app overrides
     * declared in config('inventory.bindings').
     */
    private function bindContractsFromConfig(): void
    {
        $bindings = config('inventory.bindings', []);

        $map = [
            'tenant_resolver'           => [TenantResolverContract::class,           NullTenantResolver::class],
            'policy_resolver'           => [PolicyResolverContract::class,           DefaultPolicyResolver::class],
            'document_number_generator' => [DocumentNumberGeneratorContract::class,  DefaultDocumentNumberGenerator::class],
            'stock_allocator'           => [StockAllocatorContract::class,           FefoAllocator::class],
            'stock_valuator'            => [StockValuatorContract::class,            WeightedAverageValuator::class],
            'inventory_reporter'        => [InventoryReporterContract::class,        EloquentInventoryReporter::class],
        ];

        foreach ($map as $configKey => [$contract, $default]) {
            $implementation = $bindings[$configKey] ?? $default;

            $this->app->bindIf($contract, $implementation);
        }
    }

    private function registerEventListeners(): void
    {
        $listener = \Noman\Inventory\Infrastructure\Listeners\UpdateStockBalanceListener::class;

        $this->app['events']->listen(
            \Noman\Inventory\Domain\Inventory\Events\StockReceived::class,
            [$listener, 'handleStockReceived']
        );

        $this->app['events']->listen(
            \Noman\Inventory\Domain\Inventory\Events\StockIssued::class,
            [$listener, 'handleStockIssued']
        );

        $this->app['events']->listen(
            \Noman\Inventory\Domain\Inventory\Events\StockTransferred::class,
            [$listener, 'handleStockTransferred']
        );

        $this->app['events']->listen(
            \Noman\Inventory\Domain\Inventory\Events\StockAdjusted::class,
            [$listener, 'handleStockAdjusted']
        );

        $expiryListener = \Noman\Inventory\Infrastructure\Listeners\UpdateBatchExpirySummaryListener::class;

        $this->app['events']->listen(
            \Noman\Inventory\Domain\Inventory\Events\StockReceived::class,
            [$expiryListener, 'handleStockReceived']
        );
    }

    private function registerConsoleCommands(): void
    {
        if ($this->app->runningInConsole()) {
            // TODO: Register Artisan commands (Phase 6)
            // $this->commands([
            //     \Noman\Inventory\Console\Commands\RebuildStockBalancesCommand::class,
            //     \Noman\Inventory\Console\Commands\ExpireReservationsCommand::class,
            //     \Noman\Inventory\Console\Commands\SnapshotStockBalancesCommand::class,
            // ]);
        }
    }
}
