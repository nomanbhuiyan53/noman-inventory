<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('inventory.tables.items', 'inventory_items'), function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 64)->nullable()->index();

            // Catalog references
            $table->ulid('item_type_id')->nullable()->index();
            $table->ulid('category_id')->nullable()->index();
            $table->ulid('unit_id')->nullable()->index();
            $table->ulid('secondary_unit_id')->nullable(); // alternative unit for display

            // Identifiers
            $table->string('name');
            $table->string('code', 100)->index();       // internal item code
            $table->string('sku', 100)->nullable()->index();
            $table->string('barcode', 255)->nullable()->index();
            $table->string('barcode_type', 30)->nullable();
            $table->string('brand', 100)->nullable()->index();
            $table->text('description')->nullable();

            // Costing / pricing
            $table->decimal('standard_cost', 18, 6)->nullable();
            $table->decimal('selling_price', 18, 6)->nullable();
            $table->string('currency', 3)->default('USD');

            // Reorder thresholds
            $table->decimal('reorder_level', 18, 4)->nullable();
            $table->decimal('reorder_quantity', 18, 4)->nullable();
            $table->decimal('min_stock_level', 18, 4)->nullable();
            $table->decimal('max_stock_level', 18, 4)->nullable();

            // Industry / policy
            $table->string('industry_profile', 50)->nullable();
            $table->json('policy_overrides')->nullable();

            // Flags
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_purchasable')->default(true);
            $table->boolean('is_saleable')->default(true);
            $table->boolean('is_stockable')->default(true);

            // Host-app extensibility (arbitrary JSON metadata)
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code']);
        });

        Schema::table(config('inventory.tables.items', 'inventory_items'), function (Blueprint $table) {
            $table->foreign('item_type_id')
                ->references('id')
                ->on(config('inventory.tables.item_types', 'inventory_item_types'))
                ->nullOnDelete();

            $table->foreign('category_id')
                ->references('id')
                ->on(config('inventory.tables.categories', 'inventory_categories'))
                ->nullOnDelete();

            $table->foreign('unit_id')
                ->references('id')
                ->on(config('inventory.tables.units', 'inventory_units'))
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.items', 'inventory_items'));
    }
};
