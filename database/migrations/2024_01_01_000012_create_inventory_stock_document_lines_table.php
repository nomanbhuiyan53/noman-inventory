<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('inventory.tables.stock_document_lines', 'inventory_stock_document_lines'), function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 64)->nullable()->index();

            $table->ulid('stock_document_id')->index();
            $table->ulid('item_id')->index();
            $table->ulid('variant_id')->nullable()->index();
            $table->ulid('unit_id')->nullable();
            $table->ulid('warehouse_id')->nullable()->index();
            $table->ulid('location_id')->nullable()->index();
            $table->ulid('batch_id')->nullable()->index();

            // Array of serial number IDs consumed by this line
            $table->json('serial_ids')->nullable();

            $table->decimal('quantity', 18, 4);
            $table->decimal('unit_cost', 18, 6)->nullable();
            $table->decimal('total_cost', 18, 6)->nullable();
            $table->string('currency', 3)->default('USD');

            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            $table->foreign('stock_document_id')
                ->references('id')
                ->on(config('inventory.tables.stock_documents', 'inventory_stock_documents'))
                ->cascadeOnDelete();

            $table->foreign('item_id')
                ->references('id')
                ->on(config('inventory.tables.items', 'inventory_items'))
                ->restrictOnDelete();

            $table->foreign('batch_id')
                ->references('id')
                ->on(config('inventory.tables.batches', 'inventory_batches'))
                ->nullOnDelete();

            $table->index(['tenant_id', 'item_id'], 'inv_docline_tenant_item');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.stock_document_lines', 'inventory_stock_document_lines'));
    }
};
