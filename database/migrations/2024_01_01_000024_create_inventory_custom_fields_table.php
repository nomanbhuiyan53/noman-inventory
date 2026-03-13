<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('inventory.tables.custom_fields', 'inventory_custom_fields'), function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 64)->nullable()->index();

            // The entity this custom field belongs to (item, warehouse, batch, etc.)
            $table->string('entity_type', 50)->index();

            $table->string('name', 100);
            $table->string('field_key', 100)->index();

            // text, number, date, boolean, select, multi_select
            $table->string('field_type', 30)->default('text');

            // For select/multi_select types: JSON array of allowed values
            $table->json('options')->nullable();

            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['tenant_id', 'entity_type', 'field_key'], 'inv_custom_tenant_entity_key_uniq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.custom_fields', 'inventory_custom_fields'));
    }
};
