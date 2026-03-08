<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('inventory.tables.inventory_aging', 'inventory_inventory_aging'), function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 64)->nullable()->index();

            $table->ulid('item_id')->index();
            $table->ulid('warehouse_id')->index();

            // Aging buckets (by receipt date)
            $table->decimal('qty_0_to_30_days', 18, 4)->default(0);
            $table->decimal('qty_31_to_60_days', 18, 4)->default(0);
            $table->decimal('qty_61_to_90_days', 18, 4)->default(0);
            $table->decimal('qty_over_90_days', 18, 4)->default(0);
            $table->decimal('total_quantity', 18, 4)->default(0);

            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('item_id')
                ->references('id')
                ->on(config('inventory.tables.items', 'inventory_items'))
                ->restrictOnDelete();

            $table->unique(['tenant_id', 'item_id', 'warehouse_id'], 'unique_aging');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.inventory_aging', 'inventory_inventory_aging'));
    }
};
