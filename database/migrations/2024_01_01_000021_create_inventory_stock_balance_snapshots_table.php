<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('inventory.tables.stock_balance_snapshots', 'inventory_stock_balance_snapshots'), function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 64)->nullable()->index();

            $table->ulid('item_id')->index();
            $table->ulid('warehouse_id')->index();
            $table->ulid('location_id')->nullable();
            $table->ulid('batch_id')->nullable();

            $table->decimal('quantity_on_hand', 18, 4)->default(0);
            $table->decimal('quantity_reserved', 18, 4)->default(0);
            $table->decimal('avg_cost', 18, 6)->nullable();
            $table->decimal('total_value', 18, 6)->nullable();
            $table->string('currency', 3)->default('USD');

            // Point-in-time marker; use this + ledger entries after snapshot_at
            // to reconstruct balance at any historical point
            $table->timestamp('snapshot_at')->index();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'item_id', 'snapshot_at'], 'inv_snap_tenant_item_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.stock_balance_snapshots', 'inventory_stock_balance_snapshots'));
    }
};
