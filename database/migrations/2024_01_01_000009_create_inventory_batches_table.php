<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('inventory.tables.batches', 'inventory_batches'), function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 64)->nullable()->index();

            $table->ulid('item_id')->index();

            $table->string('batch_code', 100)->index();
            $table->string('lot_number', 100)->nullable();
            $table->string('manufactured_by', 100)->nullable();

            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable()->index();

            // Original quantity received in this batch
            $table->decimal('quantity_received', 18, 4)->default(0);

            // Standard cost for this specific batch (for batch-specific FIFO costing)
            $table->decimal('unit_cost', 18, 6)->nullable();
            $table->string('currency', 3)->default('USD');

            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->foreign('item_id')
                ->references('id')
                ->on(config('inventory.tables.items', 'inventory_items'))
                ->cascadeOnDelete();

            // A batch code must be unique per item per tenant
            $table->unique(['tenant_id', 'item_id', 'batch_code'], 'inv_batch_tenant_item_code_uniq');

            // Composite index for FEFO queries (expiry_date ASC, item_id)
            $table->index(['item_id', 'expiry_date'], 'inv_batch_item_expiry');
            $table->index(['tenant_id', 'item_id', 'expiry_date'], 'inv_batch_tenant_item_expiry');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.batches', 'inventory_batches'));
    }
};
