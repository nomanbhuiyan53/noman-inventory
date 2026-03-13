<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('inventory.tables.stock_documents', 'inventory_stock_documents'), function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 64)->nullable()->index();

            $table->string('document_number', 64)->index();

            // receive, issue, transfer, adjustment, opening, stock_count, reversal
            $table->string('document_type', 30)->index();

            // draft, pending, approved, posted, reversed, cancelled
            $table->string('status', 20)->default('draft')->index();

            // For transfers: source and destination warehouses
            $table->ulid('source_warehouse_id')->nullable()->index();
            $table->ulid('destination_warehouse_id')->nullable()->index();
            $table->ulid('source_location_id')->nullable();
            $table->ulid('destination_location_id')->nullable();

            // External reference (e.g. Sales Order ID, Purchase Order ID)
            $table->string('reference_document_number', 100)->nullable()->index();
            $table->string('reference_type', 50)->nullable()->index();
            $table->string('reference_id', 64)->nullable()->index();

            // Reversals: link to the original document
            $table->ulid('reversal_of_id')->nullable()->index();
            $table->string('reversal_reason', 500)->nullable();

            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            // Idempotency key prevents duplicate posting on network retry
            $table->string('idempotency_key', 128)->nullable();

            // Audit timestamps and user references (string IDs - package-agnostic)
            $table->string('created_by', 64)->nullable();
            $table->string('approved_by', 64)->nullable();
            $table->string('posted_by', 64)->nullable();
            $table->string('reversed_by', 64)->nullable();
            $table->string('cancelled_by', 64)->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('posted_at')->nullable()->index();
            $table->timestamp('reversed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            // Unique document number per tenant
            $table->unique(['tenant_id', 'document_number'], 'inv_doc_tenant_docnum_uniq');

            // Unique idempotency key per tenant
            $table->unique(['tenant_id', 'idempotency_key'], 'inv_doc_tenant_idempotency_uniq');

            $table->foreign('reversal_of_id')
                ->references('id')
                ->on(config('inventory.tables.stock_documents', 'inventory_stock_documents'))
                ->nullOnDelete();

            $table->foreign('source_warehouse_id')
                ->references('id')
                ->on(config('inventory.tables.warehouses', 'inventory_warehouses'))
                ->nullOnDelete();

            $table->foreign('destination_warehouse_id')
                ->references('id')
                ->on(config('inventory.tables.warehouses', 'inventory_warehouses'))
                ->nullOnDelete();

            // Composite indexes for common queries
            $table->index(['tenant_id', 'document_type', 'status'], 'inv_doc_tenant_type_status');
            $table->index(['tenant_id', 'posted_at'], 'inv_doc_tenant_posted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.stock_documents', 'inventory_stock_documents'));
    }
};
