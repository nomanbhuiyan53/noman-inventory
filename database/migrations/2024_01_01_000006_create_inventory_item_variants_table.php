<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('inventory.tables.item_variants', 'inventory_item_variants'), function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 64)->nullable()->index();

            $table->ulid('item_id')->index();

            $table->string('name');
            $table->string('sku', 100)->nullable()->index();
            $table->string('barcode', 255)->nullable();

            // Variant-specific attributes (e.g. size=L, color=Red)
            $table->json('attributes')->nullable();

            $table->decimal('price_adjustment', 18, 6)->default(0);
            $table->decimal('cost_adjustment', 18, 6)->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('item_id')
                ->references('id')
                ->on(config('inventory.tables.items', 'inventory_items'))
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.item_variants', 'inventory_item_variants'));
    }
};
