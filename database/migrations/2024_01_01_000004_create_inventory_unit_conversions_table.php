<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('inventory.tables.unit_conversions', 'inventory_unit_conversions'), function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 64)->nullable()->index();

            $table->ulid('from_unit_id')->index();
            $table->ulid('to_unit_id')->index();

            // Multiply from_unit quantity by factor to get to_unit quantity
            $table->decimal('factor', 18, 8);

            $table->timestamps();

            $table->foreign('from_unit_id')
                ->references('id')
                ->on(config('inventory.tables.units', 'inventory_units'))
                ->cascadeOnDelete();

            $table->foreign('to_unit_id')
                ->references('id')
                ->on(config('inventory.tables.units', 'inventory_units'))
                ->cascadeOnDelete();

            $table->unique(['tenant_id', 'from_unit_id', 'to_unit_id'], 'inv_unit_conv_tenant_from_to_uniq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.unit_conversions', 'inventory_unit_conversions'));
    }
};
