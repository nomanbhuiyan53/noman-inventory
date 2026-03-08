<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('inventory.tables.valuation_entries', 'inventory_valuation_entries'), function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 64)->nullable()->index();

            $table->ulid('stock_document_line_id')->index();
            $table->ulid('stock_movement_id')->index();
            $table->ulid('item_id')->index();
            $table->ulid('batch_id')->nullable()->index();

            $table->string('movement_type', 30);

            // Signed quantity (positive = in, negative = out)
            $table->decimal('quantity', 18, 4);

            $table->decimal('unit_cost', 18, 6);
            $table->decimal('total_cost', 18, 6);
            $table->string('currency', 3)->default('USD');

            // fifo, weighted_average, standard_cost
            $table->string('valuation_method', 30);

            // Running snapshot at the moment this entry was created
            $table->decimal('running_qty_on_hand', 18, 4)->default(0);
            $table->decimal('running_avg_cost', 18, 6)->nullable();
            $table->decimal('running_total_value', 18, 6)->nullable();

            // APPEND-ONLY: only created_at
            $table->timestamp('created_at')->useCurrent()->index();

            $table->foreign('stock_document_line_id')
                ->references('id')
                ->on(config('inventory.tables.stock_document_lines', 'inventory_stock_document_lines'))
                ->restrictOnDelete();

            $table->foreign('item_id')
                ->references('id')
                ->on(config('inventory.tables.items', 'inventory_items'))
                ->restrictOnDelete();

            $table->index(['tenant_id', 'item_id', 'created_at']);
            $table->index(['tenant_id', 'item_id', 'batch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.valuation_entries', 'inventory_valuation_entries'));
    }
};
