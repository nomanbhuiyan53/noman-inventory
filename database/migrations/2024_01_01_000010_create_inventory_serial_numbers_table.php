<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('inventory.tables.serial_numbers', 'inventory_serial_numbers'), function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 64)->nullable()->index();

            $table->ulid('item_id')->index();
            $table->ulid('batch_id')->nullable()->index();
            $table->ulid('warehouse_id')->nullable()->index();
            $table->ulid('location_id')->nullable()->index();

            $table->string('serial_code', 150)->index();

            // available, reserved, issued, returned, disposed, quarantined
            $table->string('status', 30)->default('available')->index();

            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->foreign('item_id')
                ->references('id')
                ->on(config('inventory.tables.items', 'inventory_items'))
                ->cascadeOnDelete();

            $table->foreign('batch_id')
                ->references('id')
                ->on(config('inventory.tables.batches', 'inventory_batches'))
                ->nullOnDelete();

            $table->foreign('warehouse_id')
                ->references('id')
                ->on(config('inventory.tables.warehouses', 'inventory_warehouses'))
                ->nullOnDelete();

            // Serial must be globally unique per item per tenant
            $table->unique(['tenant_id', 'item_id', 'serial_code'], 'inv_serial_tenant_item_code_uniq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.serial_numbers', 'inventory_serial_numbers'));
    }
};
