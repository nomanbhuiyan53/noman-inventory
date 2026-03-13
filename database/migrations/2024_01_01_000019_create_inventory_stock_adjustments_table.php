<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('inventory.tables.stock_adjustments', 'inventory_stock_adjustments'), function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 64)->nullable()->index();

            // Link to the posted stock document (once approved and posted)
            $table->ulid('stock_document_id')->nullable()->index();

            // Optional link to the stock count session that generated this adjustment
            $table->ulid('stock_count_session_id')->nullable()->index();

            $table->ulid('item_id')->index();
            $table->ulid('warehouse_id')->index();
            $table->ulid('location_id')->nullable()->index();
            $table->ulid('batch_id')->nullable()->index();

            // Signed quantity: positive = in, negative = out
            $table->decimal('adjustment_quantity', 18, 4);

            $table->string('reason', 255);

            // pending, approved, posted, rejected
            $table->string('status', 20)->default('pending')->index();

            $table->string('approved_by', 64)->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->foreign('item_id')
                ->references('id')
                ->on(config('inventory.tables.items', 'inventory_items'))
                ->restrictOnDelete();

            $table->foreign('warehouse_id')
                ->references('id')
                ->on(config('inventory.tables.warehouses', 'inventory_warehouses'))
                ->restrictOnDelete();

            $table->index(['tenant_id', 'status'], 'inv_adj_tenant_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.stock_adjustments', 'inventory_stock_adjustments'));
    }
};
