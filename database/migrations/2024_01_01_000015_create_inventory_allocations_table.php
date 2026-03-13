<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('inventory.tables.allocations', 'inventory_allocations'), function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 64)->nullable()->index();

            // An allocation can be linked to a reservation or directly to a document line
            $table->ulid('reservation_id')->nullable()->index();
            $table->ulid('stock_document_line_id')->nullable()->index();

            $table->ulid('item_id')->index();
            $table->ulid('batch_id')->nullable()->index();
            $table->ulid('serial_id')->nullable()->index();
            $table->ulid('warehouse_id')->index();
            $table->ulid('location_id')->nullable()->index();

            $table->decimal('quantity', 18, 4);

            // reservation, issue
            $table->string('allocation_type', 20)->default('issue');

            $table->timestamps();

            $table->foreign('item_id')
                ->references('id')
                ->on(config('inventory.tables.items', 'inventory_items'))
                ->restrictOnDelete();

            $table->foreign('reservation_id')
                ->references('id')
                ->on(config('inventory.tables.reservations', 'inventory_reservations'))
                ->nullOnDelete();

            $table->index(['tenant_id', 'item_id', 'warehouse_id'], 'inv_alloc_tenant_item_wh');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.allocations', 'inventory_allocations'));
    }
};
