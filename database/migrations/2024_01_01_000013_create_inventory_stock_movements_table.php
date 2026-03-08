<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('inventory.tables.stock_movements', 'inventory_stock_movements'), function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 64)->nullable()->index();

            $table->ulid('stock_document_id')->index();
            $table->ulid('stock_document_line_id')->index();
            $table->ulid('item_id')->index();
            $table->ulid('variant_id')->nullable();
            $table->ulid('warehouse_id')->nullable()->index();
            $table->ulid('location_id')->nullable()->index();
            $table->ulid('batch_id')->nullable()->index();
            $table->ulid('serial_id')->nullable()->index();
            $table->ulid('unit_id')->nullable();

            // Movement type from MovementType enum
            $table->string('movement_type', 30)->index();

            // Signed quantity: positive = stock increase, negative = stock decrease
            $table->decimal('quantity', 18, 4);

            // Valuation at time of posting
            $table->decimal('unit_cost', 18, 6)->nullable();
            $table->decimal('total_cost', 18, 6)->nullable();
            $table->string('currency', 3)->default('USD');

            // Denormalised for fast ledger queries without joining documents
            $table->string('reference_document_number', 100)->nullable();

            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            // Denormalised from document; indexed for date-range ledger queries
            $table->timestamp('posted_at')->index();

            // APPEND-ONLY: only created_at; no updated_at; never soft-deleted
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('stock_document_id')
                ->references('id')
                ->on(config('inventory.tables.stock_documents', 'inventory_stock_documents'))
                ->restrictOnDelete();

            $table->foreign('stock_document_line_id')
                ->references('id')
                ->on(config('inventory.tables.stock_document_lines', 'inventory_stock_document_lines'))
                ->restrictOnDelete();

            $table->foreign('item_id')
                ->references('id')
                ->on(config('inventory.tables.items', 'inventory_items'))
                ->restrictOnDelete();

            $table->foreign('batch_id')
                ->references('id')
                ->on(config('inventory.tables.batches', 'inventory_batches'))
                ->nullOnDelete();

            // High-frequency index for balance computation and stock card queries
            $table->index(['tenant_id', 'item_id', 'posted_at']);
            $table->index(['tenant_id', 'item_id', 'warehouse_id', 'posted_at']);
            $table->index(['tenant_id', 'item_id', 'warehouse_id', 'location_id', 'posted_at']);
            $table->index(['tenant_id', 'item_id', 'batch_id', 'posted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.stock_movements', 'inventory_stock_movements'));
    }
};
