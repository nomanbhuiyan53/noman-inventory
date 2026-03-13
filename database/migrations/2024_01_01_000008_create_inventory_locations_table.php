<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('inventory.tables.locations', 'inventory_locations');

        Schema::create($tableName, function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 64)->nullable()->index();

            $table->ulid('warehouse_id')->index();

            // Self-referencing for hierarchical zones: warehouse > zone > aisle > rack > shelf > bin
            $table->ulid('parent_id')->nullable()->index();

            $table->string('name');
            $table->string('code', 50)->index();
            $table->string('barcode', 255)->nullable();

            // location types: general, zone, aisle, rack, shelf, bin
            $table->string('type', 20)->default('general');

            $table->boolean('is_active')->default(true)->index();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['warehouse_id', 'code']);

            $table->foreign('warehouse_id')
                ->references('id')
                ->on(config('inventory.tables.warehouses', 'inventory_warehouses'))
                ->cascadeOnDelete();
        });

        Schema::table($tableName, function (Blueprint $blueprint) use ($tableName) {
            $blueprint->foreign('parent_id')
                ->references('id')
                ->on($tableName)
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.locations', 'inventory_locations'));
    }
};
