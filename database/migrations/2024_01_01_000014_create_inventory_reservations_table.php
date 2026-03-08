<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('inventory.tables.reservations', 'inventory_reservations'), function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 64)->nullable()->index();

            $table->ulid('item_id')->index();
            $table->ulid('warehouse_id')->index();
            $table->ulid('location_id')->nullable()->index();

            $table->decimal('quantity', 18, 4);

            // active, released, expired, consumed
            $table->string('status', 20)->default('active')->index();

            // Host-app reference (e.g. 'sales_order', 'production_order')
            $table->string('reference_type', 50)->nullable()->index();
            $table->string('reference_id', 64)->nullable()->index();

            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('released_at')->nullable();

            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->foreign('item_id')
                ->references('id')
                ->on(config('inventory.tables.items', 'inventory_items'))
                ->restrictOnDelete();

            $table->foreign('warehouse_id')
                ->references('id')
                ->on(config('inventory.tables.warehouses', 'inventory_warehouses'))
                ->restrictOnDelete();

            $table->index(['tenant_id', 'item_id', 'status']);
            $table->index(['tenant_id', 'reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.reservations', 'inventory_reservations'));
    }
};
