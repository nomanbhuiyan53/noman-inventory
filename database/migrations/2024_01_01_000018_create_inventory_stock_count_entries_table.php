<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('inventory.tables.stock_count_entries', 'inventory_stock_count_entries'), function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 64)->nullable()->index();

            $table->ulid('session_id')->index();
            $table->ulid('item_id')->index();
            $table->ulid('batch_id')->nullable()->index();
            $table->ulid('location_id')->nullable()->index();

            // System-calculated expected quantity at time of count
            $table->decimal('expected_quantity', 18, 4)->default(0);

            // Quantity physically counted by the counter
            $table->decimal('counted_quantity', 18, 4)->nullable();

            // Variance = counted - expected (negative = shortage, positive = surplus)
            $table->decimal('variance', 18, 4)->nullable();
            $table->decimal('variance_percentage', 8, 4)->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('session_id')
                ->references('id')
                ->on(config('inventory.tables.stock_count_sessions', 'inventory_stock_count_sessions'))
                ->cascadeOnDelete();

            $table->foreign('item_id')
                ->references('id')
                ->on(config('inventory.tables.items', 'inventory_items'))
                ->restrictOnDelete();

            // An item can only appear once per session per batch per location
            $table->unique(['session_id', 'item_id', 'batch_id', 'location_id'], 'unique_count_entry');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.stock_count_entries', 'inventory_stock_count_entries'));
    }
};
