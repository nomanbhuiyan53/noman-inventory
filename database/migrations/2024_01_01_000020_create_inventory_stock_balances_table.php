<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('inventory.tables.stock_balances', 'inventory_stock_balances'), function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 64)->nullable()->index();

            $table->ulid('item_id')->index();
            $table->ulid('warehouse_id')->index();
            $table->ulid('location_id')->nullable()->index();
            $table->ulid('batch_id')->nullable()->index();

            // The running totals maintained by projection listeners
            $table->decimal('quantity_on_hand', 18, 4)->default(0);
            $table->decimal('quantity_reserved', 18, 4)->default(0);

            // Derived: quantity_on_hand - quantity_reserved
            // Stored for fast filtering; must be kept in sync by projectors
            $table->decimal('quantity_available', 18, 4)->default(0);

            // Valuation projection
            $table->decimal('avg_cost', 18, 6)->nullable();
            $table->decimal('total_value', 18, 6)->nullable();
            $table->string('currency', 3)->default('USD');

            $table->timestamp('last_movement_at')->nullable()->index();

            // updated_at tracks when projection was last refreshed
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('item_id')
                ->references('id')
                ->on(config('inventory.tables.items', 'inventory_items'))
                ->restrictOnDelete();

            $table->foreign('warehouse_id')
                ->references('id')
                ->on(config('inventory.tables.warehouses', 'inventory_warehouses'))
                ->restrictOnDelete();

            // One balance row per item + warehouse + location + batch combination
            $table->unique(['tenant_id', 'item_id', 'warehouse_id', 'location_id', 'batch_id'], 'unique_balance');

            // Critical indexes for balance lookups (most queried table in the system)
            $table->index(['tenant_id', 'item_id', 'quantity_available'], 'inv_bal_tenant_item_avail');
            $table->index(['tenant_id', 'warehouse_id', 'quantity_on_hand'], 'inv_bal_tenant_wh_onhand');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.stock_balances', 'inventory_stock_balances'));
    }
};
