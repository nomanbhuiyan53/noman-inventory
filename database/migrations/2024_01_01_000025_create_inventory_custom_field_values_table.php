<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('inventory.tables.custom_field_values', 'inventory_custom_field_values'), function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 64)->nullable()->index();

            $table->ulid('custom_field_id')->index();
            $table->string('entity_type', 50)->index();
            $table->string('entity_id', 64)->index();

            // Stored as text; cast to the field's type when reading
            $table->text('value')->nullable();

            $table->timestamps();

            $table->foreign('custom_field_id')
                ->references('id')
                ->on(config('inventory.tables.custom_fields', 'inventory_custom_fields'))
                ->cascadeOnDelete();

            // Each entity can only have one value per custom field
            $table->unique(['custom_field_id', 'entity_type', 'entity_id'], 'unique_field_value');
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.custom_field_values', 'inventory_custom_field_values'));
    }
};
